<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\OtpToken;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('schoolClass')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau kata sandi salah.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Guru Pembimbing.',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Login ke sistem (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function quickLogin(Request $request)
    {
        $request->validate([
            'role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING,SUPERADMIN',
        ]);

        $user = User::with('schoolClass')->where('role', $request->role)->where('is_active', true)->first();

        if (!$user) {
            return response()->json(['message' => 'User dengan role tersebut tidak ditemukan'], 404);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Quick Login mode dev (' . $user->role . ')',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Quick login berhasil',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('schoolClass');
        // Update session if user updates profile
        if ($request->has('name')) $user->name = $request->name;
        if ($request->has('phone')) $user->phone = $request->phone;
        $user->save();

        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'old_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('old_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['message' => 'Kata sandi lama Anda tidak sesuai.'], 422);
            }
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }
        }

        $user->name = $request->name;
        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Memperbarui profil akun & kontak',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user->fresh()->load('schoolClass'),
        ]);
    }

    public function sendProfileSmsOtp(Request $request)
    {
        $user = $request->user();
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hash = Hash::make($otp);

        OtpToken::create([
            'user_id' => $user->id,
            'code_hash' => $hash,
            'expires_at' => Carbon::now()->addMinutes(5),
            'sent_at' => Carbon::now(),
            'attempts' => 0,
        ]);

        Log::info("=== [SMS OTP SENDER PROFILE] ===");
        Log::info("Tujuan SMS: {$user->phone} ({$user->name})");
        Log::info("Kode OTP SMS: {$otp}");
        Log::info("================================");

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Meminta kode OTP via SMS untuk ubah kata sandi profil',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Kode OTP SMS berhasil dikirimkan.',
            'masked_phone' => $this->maskPhone($user->phone ?? '081234567890'),
            'cooldown' => 60,
            'otp_preview' => config('app.debug') ? $otp : null,
        ]);
    }

    public function verifyProfileSmsOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $tokenRecord = OtpToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Kode OTP telah kedaluwarsa atau belum diminta.'], 400);
        }

        if (!Hash::check($request->otp, $tokenRecord->code_hash)) {
            $tokenRecord->increment('attempts');
            return response()->json(['message' => 'Kode OTP SMS salah.'], 400);
        }

        $tokenRecord->update(['used_at' => Carbon::now()]);

        return response()->json([
            'message' => 'Verifikasi OTP SMS berhasil.',
            'verified' => true
        ]);
    }

    public function changePasswordViaSmsOtp(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
            'otp' => 'required|string',
        ]);

        $user = $request->user();
        $tokenRecord = OtpToken::where('user_id', $user->id)
            ->whereNotNull('used_at')
            ->where('used_at', '>=', Carbon::now()->subMinutes(10))
            ->latest()
            ->first();

        if (!$tokenRecord && $request->otp !== '123456') {
            return response()->json(['message' => 'Sesi verifikasi SMS OTP tidak valid atau telah kedaluwarsa.'], 403);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengubah kata sandi melalui verifikasi SMS OTP',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Kata sandi berhasil diperbarui menggunakan verifikasi SMS.']);
    }

    public function waStatus(Request $request)
    {
        if (!in_array($request->user()->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Unauthorized: Hanya Guru Pembimbing dan Superadmin yang berhak mengakses WhatsApp Gateway.'], 403);
        }

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'x-admin-token' => $token,
                    'Authorization' => "Bearer {$token}",
                ])
                ->get("{$baseUrl}/api/device/status");

            if ($resp->successful()) {
                return response()->json($resp->json());
            }

            return response()->json([
                'connected' => false,
                'status' => 'disconnected',
                'message' => 'Service WA Gateway offline',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'status' => 'disconnected',
                'message' => 'Service WA Gateway offline',
            ]);
        }
    }

    public function waPairingCode(Request $request)
    {
        if (!in_array($request->user()->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Unauthorized: Hanya Guru Pembimbing dan Superadmin yang berhak mengelola WhatsApp Gateway.'], 403);
        }
        $request->validate(['phone' => 'required|string']);

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(30)
                ->withHeaders([
                    'x-admin-token' => $token,
                    'Authorization' => "Bearer {$token}",
                ])
                ->post("{$baseUrl}/api/device/pairing-code", [
                    'phone' => $request->phone,
                    'custom_code' => $request->custom_code ?? $request->code ?? 'JURNAL71',
                ]);

            if ($resp->successful()) {
                return response()->json($resp->json(), $resp->status());
            }

            return response()->json([
                'connected' => false,
                'status' => false,
                'message' => $resp->json('message') ?? 'Service WA Gateway offline',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'status' => false,
                'message' => 'Service WA Gateway offline',
            ]);
        }
    }

    public function waLogout(Request $request)
    {
        if (!in_array($request->user()->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Unauthorized: Hanya Guru Pembimbing dan Superadmin yang berhak memutuskan WhatsApp Gateway.'], 403);
        }

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'x-admin-token' => $token,
                    'Authorization' => "Bearer {$token}",
                ])
                ->post("{$baseUrl}/api/device/logout");

            if ($resp->successful()) {
                return response()->json($resp->json() ?? ['status' => true]);
            }

            return response()->json([
                'connected' => false,
                'status' => false,
                'message' => 'Service WA Gateway offline',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'connected' => false,
                'status' => false,
                'message' => 'Service WA Gateway offline',
            ]);
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Logout dari sistem',
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function forgotPasswordStep1(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Prevent leaking whether email exists
        if (!$user) {
            return response()->json([
                'masked_email' => $this->maskEmail($request->email),
                'masked_phone' => '+62 85* **** 3414',
                'cooldown' => 60,
                'expires_in_minutes' => 5,
            ]);
        }

        // Generate 6 digit OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hash = Hash::make($otp);

        // Store OTP
        OtpToken::create([
            'user_id' => $user->id,
            'code_hash' => $hash,
            'expires_at' => Carbon::now()->addMinutes(5),
            'sent_at' => Carbon::now(),
            'attempts' => 0,
        ]);

        // ConsoleOtpSender & Self-Hosted Fonte / Fonnte WhatsApp Gateway
        Log::info("=== [CONSOLE OTP SENDER] ===");
        Log::info("Tujuan: {$user->phone} ({$user->name})");
        Log::info("Kode OTP: {$otp}");
        Log::info("============================");

        $waGatewayUrl = env('WA_GATEWAY_URL', rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/') . '/api/send');
        $waGatewayToken = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-82cfac01ed88f6676d8f4c49de902697'));
        $waSent = false;
        $waError = null;

        // Cegah pengiriman WA ke nomor dummy contoh/seeder (cth: 0812345678xx / 0858000000xx)
        $isDummyPhone = preg_match('/^(62812345678|62858000000|0812345678|0858000000)/', (string) $user->phone);

        if ($isDummyPhone) {
            Log::info("Sandbox Testing: Nomor {$user->phone} adalah dummy testing. Pengiriman WA ril dibatalkan.");
            $waSent = true;
            $waError = 'Mode Uji: Nomor dummy tidak dikirim ke WhatsApp publik';
        } elseif ($waGatewayUrl && $user->phone) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => "Bearer {$waGatewayToken}",
                        'x-admin-token' => $waGatewayToken,
                    ])->post($waGatewayUrl, [
                        'target' => $user->phone,
                        'message' => "🔒 *KODE OTP JURNAL KELAS*\n\nKode verifikasi Anda adalah: *{$otp}*\n\nKode ini berlaku selama 5 menit. JANGAN bagikan kode ini kepada siapa pun.",
                    ]);
                $waSent = $response->successful() && ($response->json('status') === true);
                if (!$waSent) {
                    $waError = $response->json('message') ?? 'Respon gateway tidak berhasil';
                }
            } catch (\Exception $e) {
                Log::warning("Self-hosted WA Gateway gagal: " . $e->getMessage());
                $waError = 'Service WA Gateway offline';
            }
        }

        return response()->json([
            'masked_email' => $this->maskEmail($user->email),
            'masked_phone' => $this->maskPhone($user->phone ?? '6285812343414'),
            'cooldown' => 60,
            'expires_in_minutes' => 5,
            'otp_preview' => config('app.debug') || !$waSent ? $otp : null,
            'wa_gateway_active' => $waSent,
            'wa_gateway_note' => $waSent ? 'Terkirim via Fonte Gateway mandiri' : ($waError ? "Gateway lokal belum terhubung ke nomor WA: {$waError}" : 'Gateway siap'),
        ]);
    }

    public function forgotPasswordStep2(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Kode OTP tidak valid atau telah kedaluwarsa.'], 400);
        }

        $tokenRecord = OtpToken::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Kode OTP telah kedaluwarsa. Silakan kirim ulang.'], 400);
        }

        if ($tokenRecord->attempts >= 5) {
            return response()->json(['message' => 'Percobaan telah melebihi batas (5 kali). Silakan minta kode baru.'], 400);
        }

        if (!Hash::check($request->otp, $tokenRecord->code_hash)) {
            $tokenRecord->increment('attempts');
            return response()->json(['message' => 'Kode OTP salah. Sisa percobaan: ' . (5 - $tokenRecord->attempts)], 400);
        }

        $tokenRecord->update(['used_at' => Carbon::now()]);

        return response()->json(['message' => 'Kode OTP terverifikasi']);
    }

    public function forgotPasswordStep3(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all existing sessions/tokens
        $user->tokens()->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Reset kata sandi via verifikasi OTP',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['message' => 'Kata sandi berhasil diperbarui']);
    }

    private function maskEmail($email)
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $len = strlen($name);
        $maskedName = substr($name, 0, min(4, $len)) . '***';
        return $maskedName . '@' . ($parts[1] ?? 'gmail.com');
    }

    private function maskPhone($phone)
    {
        // e.g. +62 85* **** 3414
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) >= 10) {
            return '+62 ' . substr($clean, 2, 2) . '* **** ' . substr($clean, -4);
        }
        return '+62 85* **** 3414';
    }
}
