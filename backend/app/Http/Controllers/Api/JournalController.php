<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassJournal;
use App\Models\MaterialJournal;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;

class JournalController extends Controller
{
    public function getClassJournals(Request $request)
    {
        $classId = $request->input('class_id');
        $month = $request->input('month');
        $query = ClassJournal::with(['schoolClass', 'schedule.subject', 'createdBy', 'confirmedBy'])->latest('date');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($month) {
            $query->where('date', 'like', $month . '%');
        }

        $schedules = Schedule::with('subject', 'teacher')->get();

        $journals = $query->get()->map(function ($j) {
            return [
                'id' => $j->id,
                'date' => Carbon::parse($j->date)->format('Y-m-d'),
                'date_time' => Carbon::parse($j->date)->translatedFormat('d M,') . ' ' . ($j->schedule->start_time ?? '07:30'),
                'class' => $j->schoolClass->name ?? 'XI RPL 1',
                'teacher' => $j->confirmedBy->name ?? ($j->schedule->teacher->name ?? 'Budi Santoso, S.Pd'),
                'subject' => $j->schedule->subject->name ?? 'Pemrograman Web',
                'topic' => $j->notes ? explode(' | ', $j->activity)[0] : $j->activity,
                'activity' => $j->activity,
                'notes' => $j->notes,
                'status' => $j->verification_status,
                'confirmed_at' => $j->confirmed_at ? Carbon::parse($j->confirmed_at)->format('d M Y H:i') : null,
                'confirmed_by' => $j->confirmedBy->name ?? null,
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
            $sub = \App\Models\Subject::firstOrCreate(
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
        $schedule = Schedule::find($request->schedule_id);

        $classId = $request->class_id ?? ($schedule->class_id ?? ($user->class_id ?? 1));
        $date = $request->date ?? Carbon::today()->toDateString();
        $activity = $request->topic ? ($request->topic . ' - ' . $request->activity) : $request->activity;

        $journal = ClassJournal::create([
            'class_id' => $classId,
            'schedule_id' => $request->schedule_id,
            'date' => $date,
            'activity' => $activity,
            'notes' => $request->notes,
            'verification_status' => 'PENDING',
            'created_by_id' => $user->id,
        ]);

        // Notify teacher of the schedule
        $schedule = Schedule::find($request->schedule_id);
        if ($schedule && $schedule->teacher_id) {
            Notification::create([
                'user_id' => $schedule->teacher_id,
                'type' => 'JURNAL',
                'title' => 'Jurnal Kelas Baru Menunggu Konfirmasi',
                'body' => 'Sekretaris telah mengisi jurnal kelas untuk ' . ($schedule->subject->name ?? 'mapel Anda') . '.',
            ]);
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengisi Jurnal Kelas tanggal ' . $request->date,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Jurnal kelas berhasil dikirim dan menunggu verifikasi pengajar',
            'journal' => $journal,
        ]);
    }

    public function confirmClassJournal(Request $request, $id)
    {
        $user = $request->user();
        $journal = ClassJournal::findOrFail($id);

        $journal->update([
            'verification_status' => 'TERVERIFIKASI',
            'confirmed_by_id' => $user->id,
            'confirmed_at' => Carbon::now(),
        ]);

        // Notify secretary
        Notification::create([
            'user_id' => $journal->created_by_id,
            'type' => 'JURNAL',
            'title' => 'Jurnal Kelas Terverifikasi',
            'body' => 'Jurnal kelas Anda telah dikonfirmasi oleh ' . $user->name,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengonfirmasi jurnal kelas ID #' . $journal->id,
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Jurnal kelas berhasil dikonfirmasi']);
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
                'attachment_url' => $m->attachment_path ? url('storage/' . $m->attachment_path) : null,
                'attachments_count' => $m->attachment_path ? 1 : 2,
            ];
        });

        return response()->json($materials);
    }

    public function storeMaterialJournal(Request $request)
    {
        $user = $request->user();
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
}
