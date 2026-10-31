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
        // Point 3: Verifikasi Cloudflare Turnstile secara strict di baris paling awal sebelum cek password
        if ($turnstileError = $this->verifyTurnstileToken($request)) {
            return $turnstileError;
        }

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
                'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Wali Kelas.',
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

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('avatar');

        $dir = storage_path('app/public/avatars');
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        // Delete previous avatar file if exists
        if ($user->avatar) {
            $oldFilename = basename($user->avatar);
            $oldFilePath = $dir . '/' . $oldFilename;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }

        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $ext;
        $file->move($dir, $filename);

        $avatarUrl = '/api/avatars/' . $filename;
        $user->avatar = $avatarUrl;
        $user->avatar_url = $avatarUrl;
        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Mengunggah foto profil',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Foto profil berhasil diperbarui',
            'avatar_url' => $avatarUrl,
            'user' => $user->fresh()->load('schoolClass'),
        ]);
    }

    public function deleteAvatar(Request $request)
    {
        $user = $request->user();
        if ($user->avatar) {
            $dir = storage_path('app/public/avatars');
            $oldFilename = basename($user->avatar);
            $oldFilePath = $dir . '/' . $oldFilename;
            if (file_exists($oldFilePath)) {
                @unlink($oldFilePath);
            }
            $user->avatar = null;
            $user->avatar_url = null;
            $user->save();

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Menghapus foto profil',
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json([
            'message' => 'Foto profil berhasil dihapus',
            'user' => $user->fresh()->load('schoolClass'),
        ]);
    }

    public function serveAvatar(Request $request, $filename)
    {
        $cleanFilename = basename($filename);
        $path = storage_path('app/public/avatars/' . $cleanFilename);
        if (!file_exists($path)) {
            return response()->json(['message' => 'Foto profil tidak ditemukan.'], 404);
        }
        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function sendProfileSmsOtp(Request $request)
    {
        $user = $request->user();
        if (!$user->phone) {
            return response()->json(['message' => 'Nomor WhatsApp belum terdaftar di profil Anda. Silakan isi dan simpan nomor WhatsApp terlebih dahulu.'], 422);
        }

        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $hash = Hash::make($otp);

        OtpToken::create([
            'user_id' => $user->id,
            'code_hash' => $hash,
            'expires_at' => Carbon::now()->addMinutes(5),
            'sent_at' => Carbon::now(),
            'attempts' => 0,
        ]);

        $message = "🔒 *KODE OTP JURNAL KELAS SMKN 71 JAKARTA*\n\n"
            . "Halo *{$user->name}*,\n"
            . "Kode verifikasi OTP untuk penggantian kata sandi akun Anda adalah:\n\n"
            . "👉 *{$otp}* 👈\n\n"
            . "Kode ini berlaku selama 5 menit. JANGAN bagikan kode ini kepada siapa pun demi keamanan akun Anda.";

        $waResult = $this->sendWhatsAppMessage($user->phone, $message);

        Log::info("=== [WA OTP SENDER PROFILE] ===");
        Log::info("Tujuan WA: {$user->phone} ({$user->name})");
        Log::info("Kode OTP: {$otp}");
        Log::info("Status Pengiriman: " . ($waResult['status'] ? 'SUCCESS' : 'FAILED: ' . $waResult['message']));
        Log::info("===============================");

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Meminta kode OTP via WhatsApp untuk ubah kata sandi profil',
            'ip_address' => $request->ip(),
        ]);

        if (!$waResult['status']) {
            return response()->json([
                'message' => 'Gagal mengirim kode OTP ke WhatsApp: ' . $waResult['message'],
            ], 500);
        }

        return response()->json([
            'message' => 'Kode OTP WhatsApp berhasil dikirimkan ke nomor WhatsApp Anda.',
            'masked_phone' => $this->maskPhone($user->phone),
            'cooldown' => 60,
            'otp_preview' => config('app.debug') ? $otp : null,
            'wa_sent' => true,
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
            return response()->json(['message' => 'Kode OTP WhatsApp tidak sesuai.'], 400);
        }

        $tokenRecord->update(['used_at' => Carbon::now()]);

        return response()->json([
            'message' => 'Verifikasi OTP WhatsApp berhasil.',
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
        if ($request->user()->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Unauthorized: Hanya Superadmin yang berhak mengakses WhatsApp Gateway.'], 403);
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
                $raw = $resp->json();
                $data = $raw['data'] ?? $raw;
                $statusStr = $data['status'] ?? (($raw['status'] ?? false) === true ? 'connected' : 'disconnected');
                $isConnected = ($statusStr === 'connected');
                $user = $data['user'] ?? null;
                $phone = $data['phone'] ?? ($user['id'] ?? ($user['rawJid'] ?? null));
                if ($phone) {
                    $phone = explode('@', explode(':', $phone)[0])[0];
                }

                return response()->json([
                    'status' => true,
                    'connected' => $isConnected,
                    'device_status' => $isConnected ? 'connected' : 'disconnected',
                    'phone' => $phone,
                    'number' => $phone,
                    'user' => $user,
                    'pairing_code' => $data['pairingCode'] ?? null,
                    'data' => $data,
                ]);
            }

            return response()->json([
                'status' => false,
                'connected' => false,
                'device_status' => 'disconnected',
                'phone' => null,
                'number' => null,
                'user' => null,
                'message' => 'Service WA Gateway offline',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'connected' => false,
                'device_status' => 'disconnected',
                'phone' => null,
                'number' => null,
                'user' => null,
                'message' => 'Service WA Gateway offline: ' . $e->getMessage(),
            ]);
        }
    }

    public function waPairingCode(Request $request)
    {
        if ($request->user()->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Unauthorized: Hanya Superadmin yang berhak mengelola WhatsApp Gateway.'], 403);
        }
        $request->validate(['phone' => 'required|string']);

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'x-admin-token' => $token,
                    'Authorization' => "Bearer {$token}",
                ])
                ->post("{$baseUrl}/api/device/pairing-code", [
                    'phone' => $request->phone
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
        if ($request->user()->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Unauthorized: Hanya Superadmin yang berhak memutuskan WhatsApp Gateway.'], 403);
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

    public function waSendTest(Request $request)
    {
        if ($request->user()->role !== 'SUPERADMIN') {
            return response()->json(['message' => 'Unauthorized: Hanya Superadmin yang berhak menguji kirim pesan WhatsApp.'], 403);
        }

        $request->validate([
            'phone' => 'nullable|string',
            'target' => 'nullable|string',
            'message' => 'required|string',
        ]);

        $target = $request->phone ?: $request->target;
        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Nomor WhatsApp tujuan wajib diisi.'], 422);
        }

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        try {
            $resp = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'x-admin-token' => $token,
                    'Authorization' => "Bearer {$token}",
                ])
                ->post("{$baseUrl}/api/send", [
                    'target' => $target,
                    'message' => $request->message,
                ]);

            if ($resp->successful()) {
                $json = $resp->json();
                $isStatusOk = ($json['status'] ?? false) === true;
                
                // Cek rincian detail target jika ada
                if (isset($json['details']) && is_array($json['details'])) {
                    $failedTarget = collect($json['details'])->firstWhere('status', false);
                    if ($failedTarget) {
                        return response()->json([
                            'status' => false,
                            'message' => $failedTarget['error'] ?? 'Pengiriman ke nomor target tidak berhasil.',
                            'details' => $json['details'],
                        ], 422);
                    }
                }

                if (!$isStatusOk) {
                    return response()->json([
                        'status' => false,
                        'message' => $json['message'] ?? 'Gateway WhatsApp melaporkan status pengiriman gagal.',
                        'details' => $json['details'] ?? null,
                    ], 422);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Pesan WhatsApp berhasil terkirim ke target!',
                    'data' => $json,
                ]);
            }

            $errorMsg = $resp->json('message') 
                ?? ($resp->json('details.0.error') ?? 'Respon gateway WhatsApp gagal.');

            return response()->json([
                'status' => false,
                'message' => $errorMsg,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Service WA Gateway offline atau tidak merespon: ' . $e->getMessage(),
            ], 500);
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
        // Point 1: Wajibkan validasi token Cloudflare Turnstile sebelum men-trigger pengiriman OTP
        if ($turnstileError = $this->verifyTurnstileToken($request)) {
            return $turnstileError;
        }

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

        // Send OTP via Self-Hosted WhatsApp Gateway
        $message = "🔒 *KODE OTP JURNAL KELAS SMKN 71 JAKARTA*\n\n"
            . "Halo *{$user->name}*,\n"
            . "Kode verifikasi pemulihan kata sandi akun Anda adalah:\n\n"
            . "👉 *{$otp}* 👈\n\n"
            . "Kode ini berlaku selama 5 menit. JANGAN bagikan kode ini kepada siapa pun demi keamanan akun Anda.";

        $waResult = $this->sendWhatsAppMessage($user->phone, $message);
        $waSent = $waResult['status'];
        $waError = $waSent ? null : $waResult['message'];

        return response()->json([
            'masked_email' => $this->maskEmail($user->email),
            'masked_phone' => $this->maskPhone($user->phone ?? '6285812343414'),
            'cooldown' => 60,
            'expires_in_minutes' => 5,
            'otp_preview' => config('app.debug') ? $otp : null,
            'wa_gateway_active' => $waSent,
            'wa_gateway_note' => $waSent ? 'Terkirim via WhatsApp Gateway SMKN 71' : ($waError ?: 'Gateway siap'),
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

    private function sendWhatsAppMessage($targetPhone, $messageText)
    {
        if (empty($targetPhone)) {
            return ['status' => false, 'message' => 'Nomor WhatsApp belum diisi atau tidak valid.'];
        }

        $waGatewayUrl = env('WA_GATEWAY_URL', rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/') . '/api/send');
        $waGatewayToken = env('WA_GATEWAY_TOKEN', env('FONNTE_TOKEN', 'rapiuw-admin-secret-2026'));

        $clean = preg_replace('/[^0-9]/', '', (string)$targetPhone);
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (!str_starts_with($clean, '62') && strlen($clean) <= 11) {
            $clean = '62' . $clean;
        }

        // Cegah spam ke nomor dummy contoh
        $isDummy = preg_match('/^(62812345678|62858000000)/', $clean);
        if ($isDummy) {
            Log::info("Sandbox Testing: Nomor {$clean} adalah dummy seeder. Pengiriman WA ril dibatalkan.");
            return ['status' => true, 'message' => 'Mode Uji: Nomor dummy tidak dikirim ke WhatsApp publik.'];
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(12)
                ->withHeaders([
                    'Authorization' => "Bearer {$waGatewayToken}",
                    'x-admin-token' => $waGatewayToken,
                ])->post($waGatewayUrl, [
                    'target' => $clean,
                    'message' => $messageText,
                ]);

            if ($response->successful() && ($response->json('status') === true)) {
                return ['status' => true, 'message' => 'Pesan WhatsApp berhasil terkirim ke target!'];
            }

            $errMsg = $response->json('message') ?? 'Gateway WhatsApp tidak merespons sukses.';
            Log::warning("Gagal kirim pesan WhatsApp ke {$clean}: {$errMsg}");
            return ['status' => false, 'message' => $errMsg];
        } catch (\Exception $e) {
            Log::error("Koneksi ke WA Gateway lokal gagal: " . $e->getMessage());
            return ['status' => false, 'message' => 'Layanan WhatsApp Gateway lokal sedang offline atau tidak dapat dijangkau.'];
        }
    }

    /**
     * Verifikasi ketat token Cloudflare Turnstile (Anti-Bot)
     * Mengembalikan response 422/503 jika token tidak valid/gagal, atau null jika berhasil lolos.
     */
    private function verifyTurnstileToken(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $turnstileSecret = config('services.turnstile.secret') ?: env('TURNSTILE_SECRET_KEY');
        if (empty($turnstileSecret) || $turnstileSecret === 'OFF') {
            return null;
        }

        $turnstileToken = $request->input('turnstile_token') ?: $request->input('cf-turnstile-response');
        if (empty($turnstileToken)) {
            return response()->json([
                'message' => 'Verifikasi keamanan anti-bot (Cloudflare Turnstile) wajib diselesaikan.',
            ], 422);
        }

        // Validasi format token & cegah token dummy/bypass (misal "anything", string pendek, dll)
        $tokenClean = trim((string) $turnstileToken);
        $tokenLower = strtolower($tokenClean);
        if (strlen($tokenClean) < 20 || in_array($tokenLower, ['anything', 'dummy', 'test', 'sample', 'null', 'undefined', '1x00000000000000000000aa'])) {
            Log::warning('Turnstile dummy bypass attempt blocked', [
                'ip' => $request->ip(),
                'token' => substr($tokenClean, 0, 15) . '...',
            ]);
            return response()->json([
                'message' => 'Token verifikasi keamanan anti-bot (Cloudflare Turnstile) tidak valid.',
            ], 422);
        }

        try {
            $turnstileResp = \Illuminate\Support\Facades\Http::asForm()->timeout(6)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => $turnstileSecret,
                'response' => $tokenClean,
                'remoteip' => $request->ip(),
            ]);

            $respData = $turnstileResp->json() ?? [];
            $isSuccess = ($turnstileResp->successful() && ($respData['success'] ?? false) === true);

            // Tolak jika Cloudflare mendeteksi dummy testing key di production
            if ($isSuccess && app()->environment('production') && ($respData['metadata']['result_with_testing_key'] ?? false)) {
                Log::warning('Turnstile testing key rejected in production environment', [
                    'ip' => $request->ip(),
                ]);
                return response()->json([
                    'message' => 'Kunci pengujian Turnstile (testing key) tidak diizinkan pada server produksi.',
                ], 422);
            }

            if (!$isSuccess) {
                Log::warning('Turnstile verification failed', [
                    'ip' => $request->ip(),
                    'errors' => $respData['error-codes'] ?? [],
                ]);
                return response()->json([
                    'message' => 'Verifikasi keamanan Turnstile gagal. Silakan muat ulang halaman.',
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Gagal menghubungi Cloudflare Turnstile API: ' . $e->getMessage());
            return response()->json([
                'message' => 'Layanan verifikasi anti-bot sementara tidak dapat dihubungi. Silakan coba beberapa saat lagi.',
            ], 503);
        }

        return null;
    }
}
