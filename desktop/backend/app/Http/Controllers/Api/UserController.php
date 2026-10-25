<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
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
            if ($category === 'GURU') {
                $query->whereIn('role', ['GURU_PEMBIMBING', 'GURU_PENGAJAR']);
            } elseif ($category === 'SISWA') {
                $query->whereIn('role', ['SISWA', 'SEKRETARIS']);
            } elseif ($role && $role !== 'Semua Peran' && $role !== 'ALL') {
                $query->where('role', $role);
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

        if ($request->role === 'GURU_PEMBIMBING' && $request->class_id) {
            SchoolClass::where('id', $request->class_id)->update(['advisor_id' => $user->id]);
        }

        if ($request->role === 'GURU_PENGAJAR' && $request->subject_id) {
            Subject::where('id', $request->subject_id)->update(['teacher_id' => $user->id]);
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

        $user->update($request->only(['name', 'email', 'phone', 'nip_nis', 'class_id', 'specialization', 'role']));

        if ($user->role === 'GURU_PEMBIMBING' && $request->filled('class_id')) {
            SchoolClass::where('id', $request->class_id)->update(['advisor_id' => $user->id]);
        }

        if ($user->role === 'GURU_PENGAJAR' && $request->filled('subject_id')) {
            Subject::where('id', $request->subject_id)->update(['teacher_id' => $user->id]);
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Memperbarui data pengguna: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui',
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
        if ($currentUser->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Hanya Superadmin yang berhak melakukan impor data pengguna.'], 403);
        }

        $request->validate([
            'users' => 'required|array|min:1',
            'users.*.name' => 'required|string',
            'users.*.email' => 'required|email',
            'users.*.role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,SUPERADMIN',
            'users.*.nip_nis' => 'nullable|string',
            'users.*.phone' => 'nullable|string',
            'users.*.class_id' => 'nullable|exists:classes,id',
        ]);

        $created = 0;
        $skipped = 0;
        $defaultPassword = Hash::make('password123');

        foreach ($request->users as $u) {
            $existing = User::where('email', $u['email'])->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => $defaultPassword,
                'phone' => $u['phone'] ?? null,
                'nip_nis' => $u['nip_nis'] ?? null,
                'role' => $u['role'],
                'class_id' => $u['class_id'] ?? null,
                'is_active' => true,
            ]);
            $created++;
        }

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => "Impor massal pengguna: {$created} berhasil dibuat, {$skipped} dilewati",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Proses impor selesai: {$created} akun berhasil ditambahkan, {$skipped} dilewati (email sudah ada).",
            'created_count' => $created,
            'skipped_count' => $skipped,
        ]);
    }
}
