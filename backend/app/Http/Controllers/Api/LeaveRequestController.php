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
use Illuminate\Support\Str;
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
            $child = ($user->student_id ? User::find($user->student_id) : null)
                ?? User::where('nip_nis', $user->nip_nis)->where('role', 'SISWA')->first()
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
            'file' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png|mimetypes:application/pdf,image/jpeg,image/png',
        ]);

        // Siswa langsung mengajukan izin atas namanya sendiri tanpa lewat Sekretaris
        $studentId = ($user->role === 'SISWA') ? $user->id : ($request->student_id ?: $user->id);

        // Validasi: Cegah permohonan ganda jika sudah ada surat izin yang DISETUJUI atau PENDING yang tumpang tindih
        $overlapping = LeaveRequest::where('student_id', $studentId)
            ->whereIn('status', ['APPROVED', 'PENDING'])
            ->where(function ($q) use ($request) {
                $start = $request->start_date;
                $end = $request->end_date;
                $q->where(function ($sub) use ($start, $end) {
                    $sub->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end]);
                })->orWhere(function ($sub) use ($start, $end) {
                    $sub->where('start_date', '<=', $start)
                        ->where('end_date', '>=', $end);
                });
            })
            ->first();

        if ($overlapping) {
            $isApproved = ($overlapping->status === 'APPROVED');
            $statusText = $isApproved ? 'TELAH DISETUJUI' : 'MENUNGGU KONFIRMASI (PENDING)';
            return response()->json([
                'message' => 'Anda sudah memiliki permohonan surat izin yang ' . $statusText . ' untuk tanggal ' . Carbon::parse($overlapping->start_date)->translatedFormat('d M Y') . ' s/d ' . Carbon::parse($overlapping->end_date)->translatedFormat('d M Y') . '. Tidak dapat mengirimkan surat izin ganda pada rentang waktu yang sama.',
            ], 422);
        }

        $path = null;
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $ext = strtolower($uploaded->guessExtension() ?: $uploaded->getClientOriginalExtension());
            $safeName = Str::uuid()->toString() . '.' . $ext;
            $path = $uploaded->storeAs('leave_attachments', $safeName, 'public');
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

        // Hapus presensi mandiri (HADIR) yang mungkin sempat tercatat sebelumnya untuk rentang tanggal pengajuan
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $sess = AttendanceSession::where('class_id', $student ? ($student->class_id ?? 1) : 1)
                ->whereDate('date', $date->toDateString())
                ->first();
            if ($sess) {
                AttendanceRecord::where('session_id', $sess->id)
                    ->where('student_id', $studentId)
                    ->delete();
            }
        }

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
                    ['status' => $statusToRecord, 'note' => 'Surat Izin ' . $leave->type . ' disetujui Wali Kelas: ' . $leave->reason]
                );
            }
        } elseif ($request->status === 'REJECTED') {
            // Jika DITOLAK: Batalkan izin approved lain yang tumpang tindih untuk siswa ini
            LeaveRequest::where('student_id', $leave->student_id)
                ->where('id', '!=', $leave->id)
                ->where('status', 'APPROVED')
                ->where(function ($q) use ($leave) {
                    $q->whereBetween('start_date', [$leave->start_date, $leave->end_date])
                      ->orWhereBetween('end_date', [$leave->start_date, $leave->end_date]);
                })
                ->update([
                    'status' => 'REJECTED',
                    'review_note' => 'Dibatalkan karena izin ditolak oleh Wali Kelas: ' . ($request->review_note ?: 'Tidak memenuhi syarat izin.')
                ]);

            // Hapus/batalkan seluruh attendance record siswa untuk tanggal tersebut (baik HADIR lama, SAKIT, maupun IZIN)
            // agar siswa tidak tercatat hadir/izin tanpa verifikasi pasca penolakan
            $start = Carbon::parse($leave->start_date);
            $end = Carbon::parse($leave->end_date);
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $session = AttendanceSession::where('class_id', $leave->student->class_id ?? 1)
                    ->whereDate('date', $date->toDateString())
                    ->first();
                if ($session) {
                    AttendanceRecord::where('session_id', $session->id)
                        ->where('student_id', $leave->student_id)
                        ->delete();
                }
            }
        }

        // Notify student with exact reason
        $reasonText = $request->review_note ? ' Alasan: ' . $request->review_note : '';
        Notification::create([
            'user_id' => $leave->student_id,
            'type' => 'SURAT_IZIN',
            'title' => 'Surat Izin ' . ($request->status === 'APPROVED' ? 'Disetujui Wali Kelas' : 'Ditolak Wali Kelas'),
            'body' => ($request->status === 'APPROVED')
                ? 'Permohonan izin Anda telah disetujui oleh Wali Kelas (' . $user->name . ').'
                : 'Permohonan izin Anda telah ditolak oleh Wali Kelas (' . $user->name . ').' . $reasonText,
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
                $path = $altPath;
            } else {
                return response()->json(['message' => 'Berkas lampiran surat izin tidak ditemukan.'], 404);
            }
        }

        // Point 7: Access Control Policy (IDOR Protection) untuk berkas surat izin
        $user = $request->user();
        $leave = LeaveRequest::where('attachment_path', 'like', "%{$cleanFilename}")->first();
        if ($leave && $user) {
            $canAccess = in_array($user->role, ['SUPERADMIN', 'GURU_PEMBIMBING', 'GURU_PENGAJAR'])
                || $leave->student_id === $user->id
                || $leave->submitted_by_id === $user->id
                || ($user->role === 'ORANG_TUA' && $user->student_id === $leave->student_id);
            if (!$canAccess) {
                return response()->json(['message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk mengunduh lampiran surat izin ini.'], 403);
            }
        }

        return response()->file($path, [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
