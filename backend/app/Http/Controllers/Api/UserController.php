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
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang berhak mengelola data pengguna.'], 403);
        }

        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::with('schoolClass')->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip_nis', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role && $role !== 'Semua Peran') {
            $query->where('role', $role);
        }

        $users = $query->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang dapat menambah user'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'nip_nis' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $defaultPassword = 'password123';

        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'phone' => $request->phone,
            'nip_nis' => $request->nip_nis,
            'class_id' => $request->class_id,
            'password' => Hash::make($defaultPassword),
            'is_active' => true,
        ]);

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
            'user' => $user,
        ]);
    }

    public function toggleActive(Request $request, $id)
    {
        $currentUser = $request->user();
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
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
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        
        if ($user->role === 'SISWA') {
            return response()->json([
                'message' => 'Guru Pembimbing tidak diizinkan mereset kata sandi Siswa. Siswa dapat mereset mandiri melalui Lupa Password.'
            ], 403);
        }

        $newPassword = 'password123';
        $user->password = Hash::make($newPassword);
        $user->save();

        // Revoke tokens
        $user->tokens()->delete();

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Mereset kata sandi pengguna: ' . $user->name,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Kata sandi pengguna berhasil direset ke password123',
        ]);
    }

    public function getClasses()
    {
        return response()->json(SchoolClass::all());
    }

    public function update(Request $request, $id)
    {
        $currentUser = $request->user();
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang berhak mengedit data pengguna.'], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'nip_nis' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'role' => 'sometimes|required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'nip_nis', 'class_id', 'role']));

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'Memperbarui data pengguna: ' . $user->name . ' (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Data pengguna berhasil diperbarui',
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $currentUser = $request->user();
        if ($currentUser->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang berhak menghapus pengguna.'], 403);
        }

        if ($currentUser->id == $id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun Anda sendiri.'], 422);
        }

        $user = User::findOrFail($id);
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
}
