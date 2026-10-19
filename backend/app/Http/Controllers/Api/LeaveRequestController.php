<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\AttendanceSession;
use App\Models\AttendanceRecord;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $classId = $request->input('class_id');

        $query = LeaveRequest::with(['student.schoolClass', 'submittedBy', 'reviewer'])->latest();

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
                'class' => $lv->student->schoolClass->name ?? 'XI RPL 1',
                'type' => $lv->type,
                'start_date' => $lv->start_date->toDateString(),
                'end_date' => $lv->end_date->toDateString(),
                'recipient' => $lv->recipient,
                'reason' => $lv->reason,
                'status' => $lv->status,
                'attachment' => $lv->attachment_path ? basename($lv->attachment_path) : null,
                'attachment_url' => $lv->attachment_path ? url('storage/' . $lv->attachment_path) : null,
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
            'student_id' => 'required',
            'type' => 'required|in:SAKIT,IZIN,DISPENSASI',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'required|string',
            'signature' => 'nullable|string',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('leave_attachments', 'public');
        }

        $leave = LeaveRequest::create([
            'student_id' => $request->student_id,
            'submitted_by_id' => $user->id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'recipient' => $request->recipient ?: 'Guru Pembimbing Sekolah / Wali Kelas',
            'reason' => $request->reason,
            'attachment_path' => $path,
            'signature' => $request->signature,
            'status' => 'PENDING',
        ]);

        // Notify Advisor
        $student = $leave->student;
        $class = $student ? $student->schoolClass : null;
        if ($class && $class->advisor_id) {
            Notification::create([
                'user_id' => $class->advisor_id,
                'type' => 'SURAT_IZIN',
                'title' => 'Permohonan Surat Izin Baru',
                'body' => 'Sekretaris mengajukan surat izin ' . strtolower($request->type) . ' untuk ' . ($student->name ?? 'siswa') . '.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengajukan permohonan surat izin ' . $request->type . ' untuk siswa ID #' . $request->student_id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Permohonan surat izin berhasil diajukan dan menunggu persetujuan',
            'leave' => $leave,
        ]);
    }

    public function review(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang berhak memverifikasi surat izin.'], 403);
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
                    ['status' => $statusToRecord, 'note' => 'Sesuai permohonan surat izin online disetujui']
                );
            }
        }

        // Notify student & secretary
        Notification::create([
            'user_id' => $leave->student_id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin ' . ($request->status === 'APPROVED' ? 'Disetujui' : 'Ditolak'),
            'body' => 'Permohonan izin Anda telah ' . ($request->status === 'APPROVED' ? 'disetujui' : 'ditolak') . ' oleh ' . $user->name,
        ]);

        Notification::create([
            'user_id' => $leave->submitted_by_id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin ' . ($request->status === 'APPROVED' ? 'Disetujui' : 'Ditolak'),
            'body' => 'Permohonan izin untuk ' . ($leave->student->name ?? 'siswa') . ' telah ' . ($request->status === 'APPROVED' ? 'disetujui' : 'ditolak') . '.',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => ($request->status === 'APPROVED' ? 'Menyetujui' : 'Menolak') . ' surat izin ID #' . $leave->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Status surat izin berhasil diperbarui']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $leave = LeaveRequest::findOrFail($id);

        // Siswa only their own, Sekretaris or Guru Pembimbing
        if ($user->role === 'SISWA' && $leave->student_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak memiliki hak untuk membatalkan izin ini.'], 403);
        }

        if ($user->role !== 'GURU_PEMBIMBING' && $leave->status !== 'PENDING') {
            return response()->json(['message' => 'Surat izin yang telah diproses tidak dapat dibatalkan.'], 422);
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
}
