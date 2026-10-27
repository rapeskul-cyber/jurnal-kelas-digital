<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $status = $request->input('status');
        $classId = $request->input('class_id');

        $query = LeaveRequest::with(['student.schoolClass', 'submittedBy', 'reviewer'])->latest();

        // Jika login sebagai SISWA, tampilkan surat izin milik siswa itu sendiri (Alur Mandiri Siswa)
        if ($user && $user->role === 'SISWA') {
            $query->where('student_id', $user->id);
        } elseif ($user && $user->role === 'ORANG_TUA') {
            $child = User::where('nip_nis', $user->nip_nis)->where('role', 'SISWA')->first()
                ?? User::where('class_id', $user->class_id)->where('role', 'SISWA')->first();
            if ($child) {
                $query->where('student_id', $child->id);
            }
        }

        if ($status && $status !== 'Semua') {
            $query->where('status', $status);
        }

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $list = $query->get()->map(function ($lv, $idx) {
            return [
                'no' => $idx + 1,
                'id' => $lv->id,
                'date_formatted' => Carbon::parse($lv->start_date)->translatedFormat('l, d M Y'),
                'student_name' => $lv->student->name ?? 'Siswa',
                'nis' => $lv->student->nip_nis ?? '-',
                'class' => $lv->student->schoolClass->name ?? 'Kelas Industri RPL',
                'type' => $lv->type,
                'start_date' => $lv->start_date->toDateString(),
                'end_date' => $lv->end_date->toDateString(),
                'recipient' => $lv->recipient ?: 'Wali Kelas',
                'reason' => $lv->reason,
                'status' => $lv->status,
                'attachment' => $lv->attachment_path ? basename($lv->attachment_path) : null,
                'attachment_url' => $lv->attachment_path ? url('api/leave-requests/file/' . basename($lv->attachment_path)) : null,
                'review_note' => $lv->review_note,
                'reviewer_name' => $lv->reviewer->name ?? null,
            ];
        });

        return response()->json($list);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'student_id' => 'nullable',
            'type' => 'required|in:SAKIT,IZIN,DISPENSASI',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|string',
            'signature' => 'nullable|string',
        ]);

        // Siswa langsung mengajukan izin atas namanya sendiri tanpa lewat Sekretaris
        $studentId = ($user->role === 'SISWA') ? $user->id : ($request->student_id ?: $user->id);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('leave_attachments', 'public');
        }

        $leave = LeaveRequest::create([
            'student_id' => $studentId,
            'submitted_by_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'recipient' => $request->recipient ?: 'Wali Kelas',
            'reason' => $request->reason,
            'attachment_path' => $path,
            'signature' => $request->signature,
            'status' => 'PENDING',
        ]);

        // Notify Wali Kelas (Advisor)
        $student = $leave->student;
        $class = $student ? $student->schoolClass : null;
        if ($class && $class->advisor_id) {
            Notification::create([
                'user_id' => $class->advisor_id,
                'type' => 'SURAT_IZIN',
                'title' => 'Permohonan Surat Izin Siswa Baru',
                'body' => 'Siswa ' . ($student->name ?? $user->name) . ' mengajukan surat izin ' . strtolower($request->type) . ' langsung kepada Wali Kelas.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengajukan permohonan surat izin ' . $request->type . ' mandiri (' . ($student->name ?? $user->name) . ') ke Wali Kelas',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Permohonan surat izin berhasil dikirimkan langsung ke Wali Kelas dan menunggu persetujuan',
            'leave' => $leave,
        ]);
    }

    public function review(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Wali Kelas atau Superadmin yang berhak memverifikasi surat izin.'], 403);
        }
        $request->validate([
            'status' => 'required|in:APPROVED,REJECTED',
            'review_note' => 'nullable|string',
        ]);

        $leave = LeaveRequest::findOrFail($id);

        $leave->update([
            'status' => $request->status,
            'reviewer_id' => $user->id,
            'review_note' => $request->review_note,
            'reviewed_at' => Carbon::now(),
        ]);

        // If APPROVED: automatically mark student attendance as SAKIT or IZIN in attendance_records
        if ($request->status === 'APPROVED') {
            $statusToRecord = ($leave->type === 'SAKIT') ? 'SAKIT' : 'IZIN';

            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->isWeekend()) continue;

                $classId = $leave->student->class_id ?? 1;
                $dateStr = $date->toDateString();
                $session = AttendanceSession::where('class_id', $classId)
                    ->whereDate('date', $dateStr)
                    ->first();

                if (!$session) {
                    $session = AttendanceSession::create([
                        'class_id' => $classId,
                        'date' => $dateStr,
                        'status' => 'DRAFT',
                        'verification_status' => 'BELUM_KONFIRMASI',
                        'school_hours' => '07:30 - 15:30',
                        'room' => 'Lab. 1',
                    ]);
                }

                AttendanceRecord::updateOrCreate(
                    ['session_id' => $session->id, 'student_id' => $leave->student_id],
                    ['status' => $statusToRecord, 'note' => 'Sesuai surat izin siswa yang disetujui Wali Kelas']
                );
            }
        }

        // Notify student
        Notification::create([
            'user_id' => $leave->student_id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin ' . ($request->status === 'APPROVED' ? 'Disetujui Wali Kelas' : 'Ditolak Wali Kelas'),
            'body' => 'Permohonan izin Anda telah ' . ($request->status === 'APPROVED' ? 'disetujui' : 'ditolak') . ' oleh Wali Kelas (' . $user->name . ').',
        ]);

        if ($leave->submitted_by_id && $leave->submitted_by_id !== $leave->student_id) {
            Notification::create([
                'user_id' => $leave->submitted_by_id,
                'type' => 'SURAT_IZIN',
                'title' => 'Surat Izin ' . ($request->status === 'APPROVED' ? 'Disetujui' : 'Ditolak'),
                'body' => 'Permohonan izin untuk ' . ($leave->student->name ?? 'siswa') . ' telah ' . ($request->status === 'APPROVED' ? 'disetujui' : 'ditolak') . ' oleh Wali Kelas.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => ($request->status === 'APPROVED' ? 'Menyetujui' : 'Menolak') . ' surat izin siswa ID #' . $leave->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Status surat izin berhasil diperbarui oleh Wali Kelas']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $leave = LeaveRequest::findOrFail($id);

        // Siswa only their own, Sekretaris or Wali Kelas
        if ($user->role === 'SISWA' && $leave->student_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak untuk membatalkan izin ini.'], 403);
        }

        if ($user->role !== 'GURU_PEMBIMBING' && $leave->status !== 'PENDING') {
            return response()->json(['message' => 'Surat izin yang telah diproses Wali Kelas tidak dapat dibatalkan.'], 422);
        }

        if ($leave->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($leave->attachment_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($leave->attachment_path);
        }

        $leave->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Membatalkan permohonan surat izin ID #' . $id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Permohonan surat izin berhasil dibatalkan']);
    }

    public function downloadLeaveFile(Request $request, $filename)
    {
        $cleanFilename = basename($filename);
        $path = storage_path('app/public/leave_attachments/' . $cleanFilename);
        if (!file_exists($path)) {
            $altPath = storage_path('app/public/' . $cleanFilename);
            if (file_exists($altPath)) {
                return response()->file($altPath);
            }
            return response()->json(['message' => 'Berkas lampiran surat izin tidak ditemukan.'], 404);
        }
        return response()->file($path);
    }
}
