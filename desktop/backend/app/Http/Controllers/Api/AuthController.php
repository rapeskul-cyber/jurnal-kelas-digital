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
            'role' => 'required|in:SISWA,SEKRETARIS,GURU_PENGAJAR,GURU_PEMBIMBING',
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
        return response()->json($user);
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

        // ConsoleOtpSender (as required by prompt)
        Log::info("=== [CONSOLE OTP SENDER] ===");
        Log::info("Tujuan: {$user->phone} ({$user->name})");
        Log::info("Kode OTP: {$otp}");
        Log::info("============================");

        return response()->json([
            'masked_email' => $this->maskEmail($user->email),
            'masked_phone' => $this->maskPhone($user->phone ?? '6285812343414'),
            'cooldown' => 60,
            'expires_in_minutes' => 5,
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
