<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;

class JournalController extends Controller
{
    public function getClassJournals(Request $request)
    {
        $user = $request->user();
        $classId = $request->input('class_id');
        $month = $request->input('month');
        $query = ClassJournal::with(['schoolClass', 'schedule.subject', 'createdBy', 'confirmedBy'])->latest('date')->latest('id');

        if (in_array($user->role, ['SEKRETARIS', 'SISWA', 'ORANG_TUA'])) {
            $classId = $user->class_id ?: 1;
            $query->where('class_id', $classId);
        } elseif ($classId) {
            $query->where('class_id', $classId);
        }

        if ($month) {
            $query->where('date', 'like', $month . '%');
        }

        $schedules = Schedule::with('subject', 'teacher')->get();

        $journals = $query->get()->map(function ($j) {
            return [
                'id' => $j->id,
                'schedule_id' => $j->schedule_id,
                'date' => Carbon::parse($j->date)->format('Y-m-d'),
                'date_time' => Carbon::parse($j->date)->translatedFormat('d M,') . ' ' . ($j->schedule->start_time ?? '07:30'),
                'class' => $j->schoolClass->name ?? '-',
                'teacher' => $j->confirmedBy->name ?? ($j->schedule->teacher->name ?? '-'),
                'subject' => $j->schedule->subject->name ?? ($j->notes ? explode(' | ', $j->activity)[0] : '-'),
                'topic' => $j->notes ? explode(' | ', $j->activity)[0] : $j->activity,
                'activity' => $j->activity,
                'notes' => $j->notes,
                'status' => $j->verification_status,
                'confirmed_at' => $j->confirmed_at ? Carbon::parse($j->confirmed_at)->format('d M Y H:i') : null,
                'confirmed_by' => $j->confirmedBy->name ?? null,
                'created_by' => $j->createdBy->name ?? 'Siswa',
                'created_by_role' => $j->createdBy->role ?? 'SISWA',
            ];
        });

        return response()->json([
            'data' => $journals,
            'schedules' => $schedules,
        ]);
    }

    public function getSchedules(Request $request)
    {
        $user = $request->user();
        $query = Schedule::with(['schoolClass', 'subject', 'teacher'])->orderBy('start_time');
        if ($user->role === 'GURU_PENGAJAR') {
            $query->where('teacher_id', $user->id);
        } elseif (in_array($user->role, ['SEKRETARIS', 'SISWA', 'ORANG_TUA'])) {
            $query->where('class_id', $user->class_id ?: 1);
        } elseif ($user->class_id) {
            $query->where('class_id', $user->class_id);
        }
        return response()->json($query->get());
    }

    public function storeSchedule(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'class_id' => 'required',
            'day' => 'nullable|string',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $subjectId = $request->subject_id;
        if (!$subjectId && $request->subject_name) {
            $sub = Subject::firstOrCreate(
                ['name' => $request->subject_name],
                ['code' => strtoupper(substr($request->subject_name, 0, 4))]
            );
            $subjectId = $sub->id;
        }

        $schedule = Schedule::create([
            'class_id' => $request->class_id,
            'subject_id' => $subjectId ?: 1,
            'teacher_id' => $request->teacher_id ?: $user->id,
            'day' => $request->day ?: 'Senin',
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room ?: 'Lab. 1',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Menambahkan Jadwal Mengajar baru: ' . ($schedule->subject->name ?? 'Mapel'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Jadwal mengajar berhasil ditambahkan',
            'schedule' => $schedule->load(['schoolClass', 'subject', 'teacher']),
        ]);
    }

    public function storeClassJournal(Request $request)
    {
        $user = $request->user();
        // Siswa, Sekretaris, Guru Pengajar, dan Wali Kelas berhak mengisi Jurnal Kegiatan Belajar
        if (!in_array($user->role, ['SISWA', 'SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk mengisi jurnal kegiatan.'], 403);
        }

        $schedule = $request->schedule_id ? Schedule::find($request->schedule_id) : null;
        $classId = in_array($user->role, ['SEKRETARIS', 'SISWA'])
            ? ($user->class_id ?: 1)
            : ($request->class_id ?? ($schedule->class_id ?? ($user->class_id ?? 1)));

        if (!$schedule) {
            $schedule = Schedule::where('class_id', $classId)->first() ?? Schedule::first();
        }

        $date = $request->date ?? Carbon::today()->toDateString();
        $activity = $request->topic ? ($request->topic . ' - ' . $request->activity) : $request->activity;

        $isTeacher = in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN']);
        $status = $isTeacher ? 'TERVERIFIKASI' : 'PENDING';
        $confirmedById = $isTeacher ? $user->id : null;
        $confirmedAt = $isTeacher ? Carbon::now() : null;

        $journal = ClassJournal::create([
            'class_id' => $classId,
            'schedule_id' => $schedule?->id,
            'date' => $date,
            'activity' => $activity,
            'notes' => $request->notes,
            'verification_status' => $status,
            'confirmed_by_id' => $confirmedById,
            'confirmed_at' => $confirmedAt,
            'created_by_id' => $user->id,
        ]);

        // Kirim notifikasi ke Guru Pengajar & Wali Kelas jika diisi oleh Siswa / Sekretaris
        if (in_array($user->role, ['SISWA', 'SEKRETARIS'])) {
            if ($schedule && $schedule->teacher_id) {
                Notification::create([
                    'user_id' => $schedule->teacher_id,
                    'type' => 'JURNAL',
                    'title' => 'Laporan Jurnal Kegiatan Siswa Baru',
                    'body' => $user->name . ' telah melaporkan jurnal kegiatan belajar pada mapel ' . ($schedule->subject->name ?? 'Anda') . '.',
                ]);
            }
            $schoolClass = SchoolClass::find($classId);
            if ($schoolClass && $schoolClass->advisor_id && $schoolClass->advisor_id !== ($schedule->teacher_id ?? null)) {
                Notification::create([
                    'user_id' => $schoolClass->advisor_id,
                    'type' => 'JURNAL',
                    'title' => 'Laporan Jurnal Kegiatan Siswa Kelas Binaan',
                    'body' => 'Siswa ' . $user->name . ' mencatat jurnal kegiatan belajar hari ini.',
                ]);
            }
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Melaporkan Jurnal Kegiatan Belajar tanggal ' . $date . ' (' . $user->name . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => $isTeacher
                ? 'Jurnal kegiatan kelas berhasil disimpan & diverifikasi resmi'
                : 'Jurnal kegiatan belajar berhasil dilaporkan dan dapat dipantau oleh Guru, Wali Kelas, serta Orang Tua!',
            'journal' => $journal,
        ]);
    }

    public function updateClassJournal(Request $request, $id)
    {
        $user = $request->user();
        $journal = ClassJournal::findOrFail($id);

        if ($journal->verification_status === 'TERVERIFIKASI' && !in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json([
                'message' => 'Jurnal kegiatan telah diverifikasi guru. Data telah dikunci permanen (read-only).'
            ], 422);
        }

        $activity = $request->topic ? ($request->topic . ' - ' . $request->activity) : ($request->activity ?: $journal->activity);

        $journal->update([
            'activity' => $activity,
            'notes' => $request->notes !== null ? $request->notes : $journal->notes,
            'date' => $request->date ?: $journal->date,
            'schedule_id' => $request->schedule_id ?: $journal->schedule_id,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Menyunting draf jurnal kegiatan ID #' . $journal->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Jurnal kegiatan berhasil diperbarui',
            'journal' => $journal,
        ]);
    }

    public function confirmClassJournal(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru Pengajar, Wali Kelas, atau Superadmin yang berhak mengonfirmasi jurnal kegiatan.'], 403);
        }
        $journal = ClassJournal::findOrFail($id);

        $journal->update([
            'verification_status' => 'TERVERIFIKASI',
            'confirmed_by_id' => $user->id,
            'confirmed_at' => Carbon::now(),
        ]);

        Notification::create([
            'user_id' => $journal->created_by_id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Kegiatan Terverifikasi',
            'body' => 'Jurnal kegiatan belajar Anda telah dikonfirmasi oleh ' . $user->name,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengonfirmasi jurnal kegiatan ID #' . $journal->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Jurnal kegiatan berhasil dikonfirmasi']);
    }

    public function getMaterialJournals(Request $request)
    {
        $search = $request->input('search');
        $month = $request->input('month');

        $query = MaterialJournal::with(['subject', 'teacher', 'schoolClass'])->latest('date');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('topic', 'like', "%{$search}%")
                  ->orWhereHas('subject', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($month) {
            $query->where('date', 'like', "{$month}%");
        }

        $materials = $query->get()->map(function ($m) {
            $attachmentUrl = null;
            if ($m->attachment_path) {
                $attachmentUrl = url('api/materials/file/' . basename($m->attachment_path));
            }
            return [
                'id' => $m->id,
                'date_time' => Carbon::parse($m->date)->translatedFormat('d M, 07:30'),
                'teacher' => $m->teacher->name ?? 'Guru Pengajar',
                'subject' => $m->subject->name ?? 'Mata Pelajaran',
                'topic' => $m->topic,
                'plan' => $m->plan,
                'assignment' => $m->assignment,
                'attachment_path' => $m->attachment_path,
                'attachment_name' => $m->attachment_path ? basename($m->attachment_path) : 'Modul_Belajar_Kelas.pdf',
                'attachment_url' => $attachmentUrl,
                'attachments_count' => $m->attachment_path ? 1 : 0,
            ];
        });

        return response()->json($materials);
    }

    public function storeMaterialJournal(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru yang berhak mengunggah materi pelajaran.'], 403);
        }
        $request->validate([
            'topic' => 'required|string',
            'plan' => 'nullable|string',
            'assignment' => 'nullable|string',
            'file' => 'nullable|file|max:10240',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $schedule = $request->schedule_id ? Schedule::find($request->schedule_id) : null;
        $classId = $request->class_id ?: ($schedule->class_id ?? ($user->class_id ?? 1));
        $subjectId = $request->subject_id ?: ($schedule->subject_id ?? 1);
        $date = $request->date ?: Carbon::today()->toDateString();

        $attachmentPath = null;
        if ($request->hasFile('file')) {
            $attachmentPath = $request->file('file')->store('materials', 'public');
        } elseif ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('materials', 'public');
        }

        $material = MaterialJournal::create([
            'teacher_id' => $user->id,
            'class_id' => $classId,
            'subject_id' => $subjectId,
            'schedule_id' => $request->schedule_id,
            'date' => $date,
            'topic' => $request->topic,
            'plan' => $request->plan,
            'assignment' => $request->assignment,
            'attachment_path' => $attachmentPath,
        ]);

        $subjectName = Subject::find($subjectId)->name ?? 'Mata Pelajaran';
        $classStudents = User::where('class_id', $classId)
            ->whereIn('role', ['SISWA', 'SEKRETARIS', 'ORANG_TUA'])
            ->get();

        foreach ($classStudents as $std) {
            Notification::create([
                'user_id' => $std->id,
                'type' => 'JURNAL',
                'title' => 'Materi Baru: ' . $subjectName,
                'body' => $user->name . ' mengunggah materi baru "' . $request->topic . '". Silakan cek di menu Jurnal Kegiatan.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengunggah Rencana Belajar & Berkas Materi: ' . $request->topic,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Rencana belajar & berkas materi berhasil disimpan',
            'material' => $material,
        ]);
    }

    public function downloadMaterialFile(Request $request, $filename)
    {
        $cleanFilename = basename($filename);
        $path = storage_path('app/public/materials/' . $cleanFilename);
        if (!file_exists($path)) {
            return response()->json(['message' => 'Berkas lampiran materi tidak ditemukan.'], 404);
        }
        return response()->file($path);
    }

    public function deleteClassJournal(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['SISWA', 'SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Anda tidak berhak menghapus jurnal kegiatan.'], 403);
        }
        $journal = ClassJournal::findOrFail($id);
        if ($journal->verification_status === 'TERVERIFIKASI' && !in_array($user->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json([
                'message' => 'Jurnal kegiatan telah terverifikasi dan tidak dapat dihapus.'
            ], 422);
        }
        $journal->delete();
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Menghapus jurnal kegiatan ID #' . $id,
            'ip_address' => $request->ip(),
        ]);
        return response()->json(['message' => 'Jurnal kegiatan berhasil dihapus']);
    }

    public function updateMaterialJournal(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING'])) {
            return response()->json(['message' => 'Hanya Guru Pengajar atau Wali Kelas yang berhak menyunting materi pelajaran.'], 403);
        }
        $material = MaterialJournal::findOrFail($id);
        $material->update([
            'topic' => $request->topic ?: $material->topic,
            'plan' => $request->plan ?: $material->plan,
            'assignment' => $request->assignment !== null ? $request->assignment : $material->assignment,
        ]);
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Menyunting jurnal materi ID #' . $material->id,
            'ip_address' => $request->ip(),
        ]);
        return response()->json(['message' => 'Jurnal materi berhasil diperbarui', 'material' => $material]);
    }

    public function deleteMaterialJournal(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING'])) {
            return response()->json(['message' => 'Hanya Guru Pengajar atau Wali Kelas yang berhak menghapus materi pelajaran.'], 403);
        }
        $material = MaterialJournal::findOrFail($id);
        if ($material->attachment_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($material->attachment_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($material->attachment_path);
        }
        $material->delete();
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Menghapus jurnal materi ID #' . $id,
            'ip_address' => $request->ip(),
        ]);
        return response()->json(['message' => 'Materi pelajaran berhasil dihapus']);
    }
}
