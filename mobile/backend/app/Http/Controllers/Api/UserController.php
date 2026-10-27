<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Notification;
use App\Models\AuditLog;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru Pembimbing dan Superadmin yang berhak mengelola data pengguna.'], 403);
        }

        $search = $request->input('search');
        $role = $request->input('role');
        $category = $request->input('category'); // e.g. GURU, SISWA

        $query = User::with(['schoolClass', 'guidedClass', 'taughtSubjects'])->orderBy('name');

        // GURU_PEMBIMBING: ONLY see students in their own guided class!
        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            $query->where('class_id', $classId)->whereIn('role', ['SISWA', 'SEKRETARIS']);
        } else {
            // SUPERADMIN filters
            if ($role && $role !== 'Semua Peran' && $role !== 'ALL') {
                $query->where('role', $role);
            } elseif ($category === 'GURU') {
                $query->whereIn('role', ['GURU_PEMBIMBING', 'GURU_PENGAJAR']);
            } elseif ($category === 'SISWA') {
                $query->whereIn('role', ['SISWA', 'SEKRETARIS']);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip_nis', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(100);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru Pembimbing dan Superadmin yang dapat menambah user'], 403);
        }

        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if (!$classId) {
                return response()->json(['message' => 'Anda belum terdaftar sebagai wali kelas dari kelas manapun.'], 422);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'role' => 'required|in:SISWA,SEKRETARIS',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string',
                'nip_nis' => 'nullable|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'role' => $request->role,
                'email' => $request->email,
                'phone' => $request->phone,
                'nip_nis' => $request->nip_nis,
                'class_id' => $classId,
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]);

            if ($request->role === 'SEKRETARIS') {
                SchoolClass::where('id', $classId)->update(['secretary_id' => $user->id]);
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => "Menambahkan murid kelas: {$user->name} ({$user->role})",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Murid kelas baru berhasil ditambahkan',
                'user' => $user->load('schoolClass'),
            ]);
        }

        // SUPERADMIN
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,SUPERADMIN',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'nip_nis' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'specialization' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $defaultPassword = 'password123';

        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'phone' => $request->phone,
            'nip_nis' => $request->nip_nis,
            'class_id' => $request->class_id,
            'specialization' => $request->specialization,
            'password' => Hash::make($defaultPassword),
            'is_active' => true,
        ]);

        if ($request->role === 'GURU_PEMBIMBING' && $request->filled('class_id')) {
            User::where('class_id', $request->class_id)
                ->where('role', 'GURU_PEMBIMBING')
                ->where('id', '!=', $user->id)
                ->update(['class_id' => null]);
            SchoolClass::where('id', $request->class_id)->update(['advisor_id' => $user->id]);
        }

        if ($request->role === 'GURU_PENGAJAR' && $request->filled('subject_id')) {
            $sub = Subject::find($request->subject_id);
            if ($sub) {
                $sub->update(['teacher_id' => $user->id]);
                if (!$user->specialization) {
                    $user->update(['specialization' => $sub->name]);
                }
            }
        }

        Notification::create([
            'user_id' => $currentUser->id,
            'type' => 'USER',
            'title' => 'Pengguna Baru Terdaftar',
            'body' => 'Pengguna baru ' . $user->name . ' (' . $user->role . ') berhasil ditambahkan ke sistem.',
        ]);

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Menambahkan pengguna baru: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Pengguna baru berhasil ditambahkan',
            'user' => $user->load(['schoolClass', 'guidedClass', 'taughtSubjects']),
        ]);
    }

    public function toggleActive(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if ($user->class_id != $classId || !in_array($user->role, ['SISWA', 'SEKRETARIS'])) {
                return response()->json(['message' => 'Anda hanya berhak mengelola murid di kelas Anda sendiri.'], 403);
            }
        }

        $user->is_active = !$user->is_active;
        $user->save();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . ' akun pengguna: ' . $user->name,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Status pengguna berhasil diperbarui',
            'is_active' => $user->is_active,
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if ($user->class_id != $classId || !in_array($user->role, ['SISWA', 'SEKRETARIS'])) {
                return response()->json(['message' => 'Anda hanya berhak mereset kata sandi murid kelas Anda sendiri.'], 403);
            }
        }

        $newPassword = 'password123';
        $user->password = Hash::make($newPassword);
        $user->save();

        // Revoke tokens
        $user->tokens()->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Mereset kata sandi pengguna: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Kata sandi pengguna ' . $user->name . ' berhasil direset ke password123',
        ]);
    }

    public function show($id)
    {
        $user = User::with(['schoolClass', 'guidedClass', 'taughtSubjects'])->findOrFail($id);
        return response()->json($user);
    }

    public function getClasses()
    {
        return response()->json(SchoolClass::all());
    }

    public function update(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru Pembimbing dan Superadmin yang berhak mengedit data pengguna.'], 403);
        }

        $user = User::findOrFail($id);

        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if ($user->class_id != $classId || !in_array($user->role, ['SISWA', 'SEKRETARIS'])) {
                return response()->json(['message' => 'Anda hanya berhak mengedit murid di kelas Anda sendiri.'], 403);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string',
                'nip_nis' => 'nullable|string',
                'role' => 'sometimes|required|in:SISWA,SEKRETARIS',
            ]);

            $oldRole = $user->role;
            $user->update($request->only(['name', 'email', 'phone', 'nip_nis', 'role']));

            // Update secretary assignment on class
            if ($request->has('role')) {
                if ($request->role === 'SEKRETARIS') {
                    SchoolClass::where('id', $classId)->update(['secretary_id' => $user->id]);
                } elseif ($oldRole === 'SEKRETARIS' && $request->role === 'SISWA') {
                    SchoolClass::where('id', $classId)->where('secretary_id', $user->id)->update(['secretary_id' => null]);
                }
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => "Memperbarui data murid kelas: {$user->name} ({$user->role})",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Data murid kelas berhasil diperbarui',
                'user' => $user->load('schoolClass'),
            ]);
        }

        // SUPERADMIN
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'nip_nis' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'specialization' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'role' => 'sometimes|required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,SUPERADMIN',
        ]);

        $updateData = $request->only(['name', 'email', 'phone', 'nip_nis', 'specialization', 'role']);
        if ($request->has('class_id')) {
            $updateData['class_id'] = $request->class_id ?: null;
        }

        $user->update($updateData);

        $reassignedNotice = null;

        // Khusus Guru Pembimbing: Atur relasi 1:1 ke rombel binaan secara bersih
        if ($user->role === 'GURU_PEMBIMBING') {
            // 1. Lepas status wali kelas dari rombel-rombel yang sebelumnya dibina guru ini
            SchoolClass::where('advisor_id', $user->id)->update(['advisor_id' => null]);

            // 2. Jika ada class_id baru yang dipilih:
            if ($request->filled('class_id')) {
                $targetClass = SchoolClass::with('advisor')->find($request->class_id);
                $oldAdvisorId = $targetClass->advisor_id ?? null;

                // Cari guru pembimbing lain yang sebelumnya membina kelas ini
                $oldAdvisor = null;
                if ($oldAdvisorId && $oldAdvisorId != $user->id) {
                    $oldAdvisor = User::find($oldAdvisorId);
                }
                if (!$oldAdvisor) {
                    $oldAdvisor = User::where('class_id', $request->class_id)
                        ->where('role', 'GURU_PEMBIMBING')
                        ->where('id', '!=', $user->id)
                        ->first();
                }

                if ($oldAdvisor) {
                    $oldAdvisor->update(['class_id' => null]);
                    $targetClassName = $targetClass->name ?? 'kelas';
                    $reassignedNotice = "Tanggung jawab wali kelas {$targetClassName} dialihkan ke {$user->name}. Status {$oldAdvisor->name} kini menjadi 'Belum Memiliki Kelas'.";
                }

                SchoolClass::where('id', $request->class_id)->update(['advisor_id' => $user->id]);
                $user->update(['class_id' => $request->class_id]);
            } else {
                $user->update(['class_id' => null]);
            }
        }

        // Khusus Guru Pengajar: Hubungkan ke mata pelajaran
        if ($user->role === 'GURU_PENGAJAR') {
            if ($request->filled('subject_id')) {
                // Lepas mapel lama milik guru ini
                Subject::where('teacher_id', $user->id)->where('id', '!=', $request->subject_id)->update(['teacher_id' => null]);
                $sub = Subject::find($request->subject_id);
                if ($sub) {
                    $sub->update(['teacher_id' => $user->id]);
                    if (!$request->filled('specialization')) {
                        $user->update(['specialization' => $sub->name]);
                    }
                }
            } elseif ($request->has('subject_id') && !$request->subject_id) {
                Subject::where('teacher_id', $user->id)->update(['teacher_id' => null]);
            }
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Memperbarui data pengguna: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => $reassignedNotice ?: 'Data guru berhasil disimpan.',
            'user' => $user->load(['schoolClass', 'guidedClass', 'taughtSubjects']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Guru Pembimbing dan Superadmin yang berhak menghapus pengguna.'], 403);
        }

        if ($currentUser->id == $id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun Anda sendiri.'], 422);
        }

        $user = User::findOrFail($id);

        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if ($user->class_id != $classId || !in_array($user->role, ['SISWA', 'SEKRETARIS'])) {
                return response()->json(['message' => 'Anda hanya berhak menghapus murid di kelas Anda sendiri.'], 403);
            }
        }

        $userName = $user->name;
        $userRole = $user->role;

        $user->tokens()->delete();
        $user->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Menghapus pengguna: ' . $userName . ' (' . $userRole . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Pengguna berhasil dihapus',
        ]);
    }

    public function impersonate(Request $request, $id)
    {
        $currentUser = $request->user();
        if ($currentUser->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Hanya Superadmin yang berhak melakukan impersonasi akun.'], 403);
        }

        $targetUser = User::with('schoolClass')->findOrFail($id);

        $token = $targetUser->createToken('impersonate-token')->plainTextToken;

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Superadmin melakukan Login As (impersonasi) ke akun: ' . $targetUser->name . ' (' . $targetUser->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Berhasil masuk sebagai ' . $targetUser->name,
            'user' => $targetUser,
            'token' => $token,
        ]);
    }

    public function bulkImport(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['SUPERADMIN', 'GURU_PEMBIMBING'])) {
            return response()->json(['message' => 'Anda tidak memiliki hak akses untuk melakukan impor massal murid.'], 403);
        }

        $targetClassId = null;
        if ($currentUser->role === 'GURU_PEMBIMBING') {
            $targetClassId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if (!$targetClassId) {
                return response()->json(['message' => 'Anda belum terdaftar sebagai wali kelas dari kelas manapun.'], 422);
            }
        }

        $students = $request->input('students') ?? $request->input('users') ?? [];

        if (empty($students) || !is_array($students)) {
            return response()->json(['message' => 'Daftar data murid wajib diisi dan berupa array.'], 422);
        }

        $created = 0;
        $skipped = 0;
        $defaultPassword = Hash::make('password123');
        $skippedDetails = [];

        DB::beginTransaction();
        try {
            foreach ($students as $index => $u) {
                $name = trim($u['name'] ?? '');
                if (!$name) {
                    $skipped++;
                    $skippedDetails[] = "Baris " . ($index + 1) . ": Nama kosong";
                    continue;
                }

                $nipNis = trim($u['nip_nis'] ?? $u['nisn'] ?? $u['nis'] ?? '');
                $phone = trim($u['phone'] ?? $u['no_hp'] ?? $u['no_wa'] ?? '');
                $role = strtoupper(trim($u['role'] ?? 'SISWA'));
                if (!in_array($role, ['SISWA', 'SEKRETARIS'])) {
                    $role = 'SISWA';
                }

                $email = trim($u['email'] ?? '');
                if (!$email) {
                    if ($nipNis) {
                        $cleanNis = preg_replace('/[^a-zA-Z0-9]/', '', $nipNis);
                        $email = "siswa_{$cleanNis}@smkn71.sch.id";
                    } else {
                        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '.', $name)));
                        $email = "{$cleanName}." . rand(100, 999) . "@siswa.smkn71.sch.id";
                    }
                }

                // Check existing email
                $existing = User::where('email', $email)->first();
                if ($existing) {
                    $skipped++;
                    $skippedDetails[] = "{$name} ({$email}): Email sudah terdaftar";
                    continue;
                }

                $finalClassId = ($currentUser->role === 'GURU_PEMBIMBING')
                    ? $targetClassId
                    : ($u['class_id'] ?? $targetClassId);

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'phone' => $phone ?: null,
                    'nip_nis' => $nipNis ?: null,
                    'role' => $role,
                    'class_id' => $finalClassId,
                    'is_active' => true,
                ]);

                if ($role === 'SEKRETARIS' && $finalClassId) {
                    SchoolClass::where('id', $finalClassId)->update(['secretary_id' => $user->id]);
                }

                $created++;
            }

            DB::commit();

            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => "Impor massal murid: {$created} murid berhasil ditambahkan ke kelas, {$skipped} dilewati",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => "Proses impor selesai: {$created} murid berhasil ditambahkan ke kelas, {$skipped} dilewati.",
                'created_count' => $created,
                'skipped_count' => $skipped,
                'skipped_details' => $skippedDetails,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses impor massal: ' . $e->getMessage()], 500);
        }
    }
}
