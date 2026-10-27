<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Notification;
use App\Models\AuditLog;
use App\Models\Subject;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang berhak mengelola data pengguna.'], 403);
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

        $perPage = (int) $request->input('per_page', 100);
        $users = $query->paginate($perPage);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $currentUser = $request->user();
        if (!in_array($currentUser->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang dapat menambah user'], 403);
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
            'role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,ORANG_TUA,SUPERADMIN',
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
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang berhak mengedit data pengguna.'], 403);
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
            'role' => 'sometimes|required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,ORANG_TUA,SUPERADMIN',
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
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang berhak menghapus pengguna.'], 403);
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
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang berhak melakukan impor data.'], 403);
        }

        $rawList = null;

        // 1. Check if file is uploaded (.csv or .json)
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension());
            $realPath = $file->getRealPath();

            if ($ext === 'csv' || $file->getClientMimeType() === 'text/csv' || $file->getClientMimeType() === 'text/plain') {
                $rawList = [];
                if (($handle = fopen($realPath, 'r')) !== false) {
                    $header = null;
                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($row) === 1 && str_contains($row[0], ';')) {
                            $row = str_getcsv($row[0], ';');
                        }
                        if (!$header) {
                            $header = array_map(fn($h) => strtolower(trim($h)), $row);
                        } else {
                            if (count($row) >= count($header)) {
                                $rawList[] = array_combine(array_slice($header, 0, count($row)), array_slice($row, 0, count($header)));
                            }
                        }
                    }
                    fclose($handle);
                }
            } else {
                $content = file_get_contents($realPath);
                $json = json_decode($content, true);
                if (is_array($json)) {
                    $rawList = isset($json['users']) && is_array($json['users']) ? $json['users'] : (isset($json['data']) && is_array($json['data']) ? $json['data'] : $json);
                } else {
                    return response()->json(['message' => 'Berkas tidak valid atau rusak (harus CSV atau JSON).'], 422);
                }
            }
        } elseif ($request->has('users')) {
            $rawList = $request->input('users');
            if (is_string($rawList)) {
                $rawList = json_decode($rawList, true);
            }
        } else {
            // Check if request body itself is a JSON array
            $body = $request->getContent();
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $rawList = isset($decoded['users']) && is_array($decoded['users']) ? $decoded['users'] : (isset($decoded['data']) && is_array($decoded['data']) ? $decoded['data'] : $decoded);
            }
        }

        if (!is_array($rawList) || empty($rawList)) {
            return response()->json(['message' => 'Format data tidak valid. Sertakan array data pengguna dalam format JSON.'], 422);
        }

        $isPembimbing = $currentUser->role === 'GURU_PEMBIMBING';
        $classId = null;

        if ($isPembimbing) {
            $classId = $currentUser->class_id ?? SchoolClass::where('advisor_id', $currentUser->id)->value('id');
            if (!$classId) {
                return response()->json(['message' => 'Anda belum terdaftar sebagai wali kelas dari kelas binaan manapun.'], 422);
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $defaultPassword = Hash::make('password123');
        $errors = [];

        foreach ($rawList as $index => $u) {
            if (!is_array($u)) continue;

            $name = trim($u['name'] ?? ($u['nama'] ?? ''));
            if (!$name) {
                $errors[] = "Baris " . ($index + 1) . ": Nama wajib diisi.";
                $skipped++;
                continue;
            }

            $nipNis = isset($u['nip_nis']) ? trim((string)$u['nip_nis']) : (isset($u['nis']) ? trim((string)$u['nis']) : (isset($u['nip']) ? trim((string)$u['nip']) : (isset($u['nisn']) ? trim((string)$u['nisn']) : null)));
            $phone = isset($u['phone']) ? trim((string)$u['phone']) : (isset($u['no_hp']) ? trim((string)$u['no_hp']) : (isset($u['telp']) ? trim((string)$u['telp']) : null));
            $specialization = isset($u['specialization']) ? trim((string)$u['specialization']) : (isset($u['keahlian']) ? trim((string)$u['keahlian']) : (isset($u['mapel']) ? trim((string)$u['mapel']) : null));

            // Determine role & class
            if ($isPembimbing) {
                $role = isset($u['role']) && strtoupper(trim($u['role'])) === 'SEKRETARIS' ? 'SEKRETARIS' : 'SISWA';
                $targetClassId = $classId;
            } else {
                $inputRole = isset($u['role']) ? strtoupper(trim($u['role'])) : '';
                if (in_array($inputRole, ['SISWA', 'SEKRETARIS', 'GURU_PENGAJAR', 'GURU_PEMBIMBING', 'ORANG_TUA', 'SUPERADMIN'])) {
                    $role = $inputRole;
                } else {
                    $role = 'GURU_PENGAJAR';
                }

                $targetClassId = $u['class_id'] ?? null;
                if (!$targetClassId && !empty($u['class_name'])) {
                    $foundClass = SchoolClass::where('name', 'like', '%' . trim($u['class_name']) . '%')->first();
                    $targetClassId = $foundClass?->id;
                }
            }

            // Determine email
            $email = isset($u['email']) ? strtolower(trim($u['email'])) : '';
            if (!$email) {
                $slug = preg_replace('/[^a-z0-9]/', '', strtolower($nipNis ?: $name));
                $emailDomain = ($role === 'SISWA' || $role === 'SEKRETARIS') ? 'siswa.sekolah.sch.id' : 'sekolah.sch.id';
                $email = "{$slug}@{$emailDomain}";
            }

            // Check if user already exists
            $existing = User::where('email', $email)
                ->when($nipNis, function ($q) use ($nipNis) {
                    $q->orWhere('nip_nis', $nipNis);
                })
                ->first();

            if ($existing) {
                if ($isPembimbing && $existing->class_id != $classId && in_array($existing->role, ['GURU_PENGAJAR', 'GURU_PEMBIMBING', 'SUPERADMIN'])) {
                    $skipped++;
                    $errors[] = "Siswa '{$name}' memiliki email/NISN yang bentrok dengan staf/guru.";
                    continue;
                }

                $updateData = [
                    'name' => $name,
                    'phone' => $phone ?: $existing->phone,
                    'nip_nis' => $nipNis ?: $existing->nip_nis,
                    'role' => $role,
                ];

                if ($targetClassId) {
                    $updateData['class_id'] = $targetClassId;
                }
                if ($specialization) {
                    $updateData['specialization'] = $specialization;
                }

                $existing->update($updateData);

                if ($role === 'SEKRETARIS' && $targetClassId) {
                    SchoolClass::where('id', $targetClassId)->update(['secretary_id' => $existing->id]);
                }
                if ($role === 'GURU_PEMBIMBING' && $targetClassId) {
                    SchoolClass::where('id', $targetClassId)->update(['advisor_id' => $existing->id]);
                }

                $updated++;
            } else {
                $newUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $defaultPassword,
                    'phone' => $phone,
                    'nip_nis' => $nipNis,
                    'role' => $role,
                    'class_id' => $targetClassId,
                    'specialization' => $specialization,
                    'is_active' => true,
                ]);

                if ($role === 'SEKRETARIS' && $targetClassId) {
                    SchoolClass::where('id', $targetClassId)->update(['secretary_id' => $newUser->id]);
                }

                if ($role === 'GURU_PEMBIMBING' && $targetClassId) {
                    SchoolClass::where('id', $targetClassId)->update(['advisor_id' => $newUser->id]);
                }

                // If subject_id provided or mapel name matches, link teacher to subject
                $subjectId = $u['subject_id'] ?? null;
                if (!$subjectId && $specialization) {
                    $sub = Subject::where('name', 'like', "%{$specialization}%")->first();
                    $subjectId = $sub?->id;
                }
                if ($role === 'GURU_PENGAJAR' && $subjectId) {
                    Subject::where('id', $subjectId)->update(['teacher_id' => $newUser->id]);
                }

                $created++;
            }
        }

        $logAction = $isPembimbing
            ? "Impor massal murid kelas: {$created} baru ditambahkan, {$updated} diperbarui"
            : "Impor massal pengguna/guru: {$created} baru ditambahkan, {$updated} diperbarui, {$skipped} dilewati";

        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => $logAction,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Proses impor selesai: {$created} data baru ditambahkan, {$updated} data diperbarui" . ($skipped ? ", {$skipped} dilewati" : '') . ".",
            'created_count' => $created,
            'updated_count' => $updated,
            'skipped_count' => $skipped,
            'errors' => $errors,
        ]);
    }
}
