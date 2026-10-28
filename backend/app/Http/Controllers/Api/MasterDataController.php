<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Term;
use App\Models\User;
use App\Models\AuditLog;

class MasterDataController extends Controller
{
    private function checkSuperadmin(Request $request)
    {
        if ($request->user()->role !== 'SUPERADMIN') {
            abort(response()->json(['message' => 'Hanya Superadmin yang berhak mengelola data master institusi.'], 403));
        }
    }

    // ==========================================
    // 1. CLASSES (ROMBEL)
    // ==========================================

    public function getClasses(Request $request)
    {
        // Sistem dikhususkan secara eksklusif untuk Kelas Industri RPL
        $user = $request->user();
        $query = SchoolClass::with(['advisor', 'secretary'])
            ->withCount(['students', 'schedules']);

        if ($user && $user->role === 'GURU_PEMBIMBING') {
            $classId = $user->class_id ?? SchoolClass::where('advisor_id', $user->id)->value('id') ?? 1;
            $query->where('id', $classId);
        } else {
            $query->where('major', 'RPL')->orderBy('name');
        }

        return response()->json($query->get());
    }

    public function storeClass(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'name' => 'required|string|unique:classes,name',
            'major' => 'nullable|string|in:RPL',
            'advisor_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
        ]);

        $cls = SchoolClass::create([
            'name' => $request->name,
            'major' => 'RPL',
            'advisor_id' => $request->advisor_id,
            'secretary_id' => $request->secretary_id,
        ]);

        // If advisor assigned, set class_id for that user
        if ($cls->advisor_id) {
            User::where('id', $cls->advisor_id)->update(['class_id' => $cls->id]);
        }

        // If secretary assigned, set class_id for that user
        if ($cls->secretary_id) {
            User::where('id', $cls->secretary_id)->update(['class_id' => $cls->id, 'role' => 'SEKRETARIS']);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Membuat rombongan belajar baru: {$cls->name} (Jurusan {$cls->major})",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Kelas {$cls->name} berhasil ditambahkan",
            'class' => $cls->load(['advisor', 'secretary']),
        ]);
    }

    public function updateClass(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $cls = SchoolClass::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:classes,name,' . $cls->id,
            'major' => 'nullable|string|in:RPL',
            'advisor_id' => 'nullable|exists:users,id',
            'secretary_id' => 'nullable|exists:users,id',
        ]);

        $cls->update([
            'name' => $request->name,
            'major' => 'RPL',
            'advisor_id' => $request->advisor_id,
            'secretary_id' => $request->secretary_id,
        ]);

        if ($cls->advisor_id) {
            User::where('id', $cls->advisor_id)->update(['class_id' => $cls->id]);
        }

        if ($cls->secretary_id) {
            User::where('id', $cls->secretary_id)->update(['class_id' => $cls->id, 'role' => 'SEKRETARIS']);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Memperbarui data kelas: {$cls->name} (RPL)",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Kelas {$cls->name} berhasil diperbarui",
            'class' => $cls->load(['advisor', 'secretary']),
        ]);
    }

    public function destroyClass(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $cls = SchoolClass::findOrFail($id);
        $name = $cls->name;

        // Unlink users from this class
        User::where('class_id', $cls->id)->update(['class_id' => null]);
        $cls->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Menghapus rombongan belajar: {$name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => "Kelas {$name} berhasil dihapus"]);
    }

    public function promoteStudents(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'source_class_id' => 'required|exists:classes,id',
            'target_class_id' => 'required|exists:classes,id',
            'student_ids' => 'nullable|array',
        ]);

        $source = SchoolClass::findOrFail($request->source_class_id);
        $target = SchoolClass::findOrFail($request->target_class_id);

        $query = User::where('class_id', $source->id)->whereIn('role', ['SISWA', 'SEKRETARIS']);
        if (!empty($request->student_ids)) {
            $query->whereIn('id', $request->student_ids);
        }

        $count = $query->count();
        $query->update(['class_id' => $target->id]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Memindahkan {$count} siswa dari {$source->name} ke {$target->name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Berhasil memindahkan {$count} siswa dari {$source->name} ke {$target->name}",
            'transferred_count' => $count,
        ]);
    }

    // ==========================================
    // 2. SUBJECTS (MATA PELAJARAN)
    // ==========================================

    public function getSubjects(Request $request)
    {
        // Khusus mata pelajaran kejuruan RPL dan muatan umum
        $query = Subject::with(['teacher'])
            ->withCount('schedules')
            ->whereIn('major', ['RPL', 'UMUM'])
            ->orderBy('name');

        return response()->json($query->get());
    }

    public function storeSubject(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|max:20',
            'major' => 'nullable|string|in:RPL,UMUM',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $code = $request->code ?: strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $request->name), 0, 4));

        $sub = Subject::create([
            'name' => $request->name,
            'code' => $code,
            'major' => $request->major ?: 'RPL',
            'teacher_id' => $request->teacher_id,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Menambahkan mata pelajaran: {$sub->name} ({$sub->code})",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Mata pelajaran {$sub->name} berhasil ditambahkan",
            'subject' => $sub->load('teacher'),
        ]);
    }

    public function updateSubject(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $sub = Subject::findOrFail($id);

        $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string|max:20',
            'major' => 'nullable|string',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $sub->update($request->only(['name', 'code', 'major', 'teacher_id']));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Memperbarui mata pelajaran: {$sub->name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Mata pelajaran {$sub->name} berhasil diperbarui",
            'subject' => $sub->load('teacher'),
        ]);
    }

    public function destroySubject(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $sub = Subject::findOrFail($id);
        $name = $sub->name;
        $sub->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Menghapus mata pelajaran: {$name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => "Mata pelajaran {$name} berhasil dihapus"]);
    }

    // ==========================================
    // 3. SCHEDULES (JADWAL PELAJARAN)
    // ==========================================

    public function getSchedules(Request $request)
    {
        $classId = $request->input('class_id');
        $major = $request->input('major');

        $query = Schedule::with(['schoolClass', 'subject', 'teacher'])->orderBy('day')->orderBy('start_time');

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($major && $major !== 'ALL') {
            $query->whereHas('schoolClass', function ($q) use ($major) {
                $q->where('major', $major);
            });
        }

        return response()->json($query->get());
    }

    public function storeSchedule(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'room' => 'nullable|string',
        ]);

        $sch = Schedule::create([
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'teacher_id' => $request->teacher_id,
            'day' => $request->day,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'room' => $request->room ?: 'Lab. 1',
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Membuat jadwal pelajaran baru untuk kelas ID {$request->class_id}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Jadwal pelajaran berhasil ditambahkan',
            'schedule' => $sch->load(['schoolClass', 'subject', 'teacher']),
        ]);
    }

    public function updateSchedule(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $sch = Schedule::findOrFail($id);

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:users,id',
            'day' => 'required|string',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'room' => 'nullable|string',
        ]);

        $sch->update($request->only(['class_id', 'subject_id', 'teacher_id', 'day', 'start_time', 'end_time', 'room']));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Memperbarui jadwal pelajaran ID #{$sch->id}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Jadwal pelajaran berhasil diperbarui',
            'schedule' => $sch->load(['schoolClass', 'subject', 'teacher']),
        ]);
    }

    public function destroySchedule(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $sch = Schedule::findOrFail($id);
        $sch->delete();

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Menghapus jadwal pelajaran ID #{$id}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Jadwal pelajaran berhasil dihapus']);
    }

    // ==========================================
    // 4. TERMS (SEMESTER & TAHUN AJARAN)
    // ==========================================

    public function getTerms()
    {
        return response()->json(Term::orderBy('start_date', 'desc')->get());
    }

    public function storeTerm(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'effective_days' => 'required|integer',
            'is_active' => 'boolean',
        ]);

        if ($request->is_active) {
            Term::query()->update(['is_active' => false]);
        }

        $term = Term::create($request->only(['name', 'start_date', 'end_date', 'effective_days', 'is_active']));

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Menambahkan tahun ajaran/semester baru: {$term->name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Semester {$term->name} berhasil ditambahkan",
            'term' => $term,
        ]);
    }

    public function activateTerm(Request $request, $id)
    {
        $this->checkSuperadmin($request);

        $term = Term::findOrFail($id);
        Term::query()->update(['is_active' => false]);
        $term->update(['is_active' => true]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Mengaktifkan tahun ajaran/semester: {$term->name}",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Semester {$term->name} sekarang menjadi semester aktif",
            'term' => $term,
        ]);
    }
}
