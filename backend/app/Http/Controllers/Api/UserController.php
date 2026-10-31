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

        $query = User::with(['schoolClass', 'guidedClass', 'taughtSubjects', 'student.schoolClass', 'parentUser'])->orderBy('name');

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
                'parent_name' => 'nullable|string|max:255',
                'parent_phone' => 'nullable|string|max:30',
                'parent_email' => 'nullable|email',
            ]);

            // Validasi Kuota Sekretaris (Maksimal 2 orang per kelas)
            if ($request->role === 'SEKRETARIS') {
                $secCount = User::where('class_id', $classId)->where('role', 'SEKRETARIS')->count();
                if ($secCount >= 2 && !$request->filled('replace_secretary_id')) {
                    $existingSecs = User::where('class_id', $classId)->where('role', 'SEKRETARIS')->get(['id', 'name']);
                    return response()->json([
                        'message' => 'Kuota sekretaris kelas sudah penuh (maksimal 2 orang). Pilih salah satu sekretaris lama untuk digantikan.',
                        'secretaries' => $existingSecs,
                        'requires_replacement' => true
                    ], 422);
                }

                if ($request->filled('replace_secretary_id')) {
                    User::where('id', $request->replace_secretary_id)
                        ->where('class_id', $classId)
                        ->where('role', 'SEKRETARIS')
                        ->update(['role' => 'SISWA']);
                }
            }

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

            // Integrasi Akun Orang Tua oleh Wali Kelas (Dibuat otomatis jika data ortu diisi)
            if ($request->filled('parent_name') || $request->filled('parent_phone') || $request->filled('parent_email')) {
                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $user->name);
                $defaultParentEmail = 'ortu.' . strtolower($cleanName ?: 'siswa' . $user->id) . '@sekolah.sch.id';
                $parentEmail = $request->input('parent_email') ?: $defaultParentEmail;

                User::create([
                    'name' => $request->input('parent_name') ?: 'Bapak/Ibu ' . $user->name,
                    'email' => $parentEmail,
                    'phone' => $request->input('parent_phone'),
                    'password' => Hash::make('password123'),
                    'role' => 'ORANG_TUA',
                    'student_id' => $user->id,
                    'class_id' => $classId,
                    'nip_nis' => $user->nip_nis,
                    'specialization' => 'Wali Murid: ' . $user->name,
                    'is_active' => true,
                ]);
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => "Menambahkan murid kelas: {$user->name} ({$user->role})",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Murid kelas baru berhasil ditambahkan beserta akun Orang Tua',
                'user' => $user->load(['schoolClass', 'parentUser']),
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
            'student_id' => 'nullable|exists:users,id',
            'specialization' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $defaultPassword = 'password123';

        $studentId = $request->input('student_id');
        $classId = $request->input('class_id');
        $nipNis = $request->input('nip_nis');
        $spec = $request->input('specialization');

        if ($request->role === 'ORANG_TUA' && $studentId) {
            $student = User::find($studentId);
            if ($student) {
                $classId = $student->class_id ?: $classId;
                $nipNis = $nipNis ?: $student->nip_nis;
                $spec = $spec ?: "Wali Murid: {$student->name}";
            }
        }

        $user = User::create([
            'name' => $request->name,
            'role' => $request->role,
            'email' => $request->email,
            'phone' => $request->phone,
            'nip_nis' => $nipNis,
            'class_id' => $classId,
            'student_id' => $studentId,
            'specialization' => $spec,
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
                'parent_name' => 'nullable|string|max:255',
                'parent_phone' => 'nullable|string|max:30',
                'parent_email' => 'nullable|email',
                'parent_password' => 'nullable|string|min:6',
                'replace_secretary_id' => 'nullable|exists:users,id',
            ]);

            $oldRole = $user->role;

            // 1. Validasi Aturan Kuota Sekretaris (Maksimal 2 orang, Minimal 1 orang)
            if ($request->has('role')) {
                $newRole = $request->role;

                if ($newRole === 'SEKRETARIS' && $oldRole !== 'SEKRETARIS') {
                    // Cek jumlah sekretaris yang sedang aktif di kelas ini selain siswa ini
                    $activeSecs = User::where('class_id', $classId)
                        ->where('role', 'SEKRETARIS')
                        ->where('id', '!=', $user->id)
                        ->get();

                    if ($activeSecs->count() >= 2 && !$request->filled('replace_secretary_id')) {
                        return response()->json([
                            'message' => 'Kuota sekretaris kelas sudah penuh (maksimal 2 orang). Pilih salah satu sekretaris lama yang akan digantikan menjadi Siswa Biasa.',
                            'secretaries' => $activeSecs->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                            'requires_replacement' => true,
                        ], 422);
                    }

                    if ($request->filled('replace_secretary_id')) {
                        // Turunkan sekretaris lama yang dipilih menjadi Siswa Biasa
                        User::where('id', $request->replace_secretary_id)
                            ->where('class_id', $classId)
                            ->where('role', 'SEKRETARIS')
                            ->update(['role' => 'SISWA']);
                    }
                } elseif ($newRole === 'SISWA' && $oldRole === 'SEKRETARIS') {
                    // Cek apakah ini satu-satunya sekretaris di kelas
                    $remainingSecs = User::where('class_id', $classId)
                        ->where('role', 'SEKRETARIS')
                        ->where('id', '!=', $user->id)
                        ->count();

                    if ($remainingSecs < 1 && !$request->boolean('force_demote')) {
                        return response()->json([
                            'message' => 'Tidak dapat mengubah sekretaris menjadi siswa biasa. Kelas wajib memiliki minimal 1 orang sekretaris aktif untuk operasional presensi & agenda KBM! Silakan tunjuk sekretaris pengganti terlebih dahulu.',
                            'min_secretaries_violation' => true,
                        ], 422);
                    }
                }
            }

            $user->update($request->only(['name', 'email', 'phone', 'nip_nis', 'role']));

            // Update penanggung jawab sekretaris di data tabel kelas
            if ($request->has('role')) {
                if ($request->role === 'SEKRETARIS') {
                    SchoolClass::where('id', $classId)->update(['secretary_id' => $user->id]);
                } elseif ($oldRole === 'SEKRETARIS' && $request->role === 'SISWA') {
                    $otherSec = User::where('class_id', $classId)->where('role', 'SEKRETARIS')->where('id', '!=', $user->id)->first();
                    SchoolClass::where('id', $classId)->update(['secretary_id' => $otherSec?->id]);
                }
            }

            // 2. Integrasi & Pembaruan Akun Orang Tua oleh Wali Kelas
            if ($request->filled('parent_name') || $request->filled('parent_phone') || $request->filled('parent_email') || $request->filled('parent_password')) {
                $parent = User::where('role', 'ORANG_TUA')
                    ->where(function($q) use ($user) {
                        $q->where('student_id', $user->id)
                          ->orWhere('nip_nis', $user->nip_nis);
                    })
                    ->first();

                $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $user->name);
                $defaultParentEmail = 'ortu.' . strtolower($cleanName ?: 'siswa' . $user->id) . '@sekolah.sch.id';
                $parentEmail = $request->input('parent_email') ?: ($parent ? $parent->email : $defaultParentEmail);

                if (!$parent) {
                    $parent = User::create([
                        'name' => $request->input('parent_name') ?: 'Bapak/Ibu ' . $user->name,
                        'email' => $parentEmail,
                        'phone' => $request->input('parent_phone'),
                        'password' => Hash::make($request->input('parent_password', 'password123')),
                        'role' => 'ORANG_TUA',
                        'student_id' => $user->id,
                        'class_id' => $classId,
                        'nip_nis' => $user->nip_nis,
                        'specialization' => 'Wali Murid: ' . $user->name,
                        'is_active' => true,
                    ]);
                } else {
                    $parentData = [
                        'name' => $request->input('parent_name') ?: $parent->name,
                        'phone' => $request->input('parent_phone') !== null ? $request->input('parent_phone') : $parent->phone,
                        'student_id' => $user->id,
                        'class_id' => $classId,
                        'nip_nis' => $user->nip_nis,
                        'specialization' => 'Wali Murid: ' . $user->name,
                    ];
                    if ($request->filled('parent_email') && $request->parent_email !== $parent->email) {
                        $parentData['email'] = $request->parent_email;
                    }
                    if ($request->filled('parent_password')) {
                        $parentData['password'] = Hash::make($request->parent_password);
                    }
                    $parent->update($parentData);
                }
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'action' => "Memperbarui data murid & akun orang tua: {$user->name} ({$user->role})",
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Data murid kelas dan akun orang tua berhasil diperbarui',
                'user' => $user->load(['schoolClass', 'parentUser']),
            ]);
        }

        // SUPERADMIN
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'nip_nis' => 'nullable|string',
            'class_id' => 'nullable|exists:classes,id',
            'student_id' => 'nullable|exists:users,id',
            'specialization' => 'nullable|string|max:100',
            'subject_id' => 'nullable|exists:subjects,id',
            'role' => 'sometimes|required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,ORANG_TUA,SUPERADMIN',
        ]);

        $updateData = $request->only(['name', 'email', 'phone', 'nip_nis', 'class_id', 'student_id', 'specialization', 'role']);
        
        $targetRole = $request->input('role', $user->role);
        if ($targetRole === 'ORANG_TUA' && $request->has('student_id')) {
            $student = $request->student_id ? User::find($request->student_id) : null;
            if ($student) {
                $updateData['class_id'] = $student->class_id ?: $user->class_id;
                $updateData['nip_nis'] = $student->nip_nis ?: $user->nip_nis;
                $updateData['specialization'] = "Wali Murid: {$student->name}";
            }
        }

        $user->update($updateData);

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
            'user' => $user->load(['schoolClass', 'guidedClass', 'taughtSubjects', 'student']),
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
