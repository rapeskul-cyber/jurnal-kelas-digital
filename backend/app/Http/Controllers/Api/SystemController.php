<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Broadcast;
use App\Models\User;
use App\Models\Notification;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SystemController extends Controller
{
    private function checkSuperadmin(Request $request)
    {
        if ($request->user()->role !== 'SUPERADMIN') {
            abort(response()->json(['message' => 'Hanya Superadmin yang berhak mengakses pengaturan sistem.'], 403));
        }
    }

    // ==========================================
    // 0. PUBLIC APP INFO (SaaS White-labeling)
    // ==========================================

    public function getPublicInfo()
    {
        $activeTerm = \App\Models\Term::where('is_active', true)->first();
        $majors = \App\Models\SchoolClass::whereNotNull('major')
            ->distinct()
            ->pluck('major')
            ->values();

        return response()->json([
            'school_name' => Setting::get('school_name', 'SMK Digital Terpadu'),
            'school_npsn' => Setting::get('school_npsn', ''),
            'school_address' => Setting::get('school_address', ''),
            'school_logo_url' => Setting::get('school_logo_url', '/images/logo-smkn71.png'),
            'headmaster_name' => Setting::get('headmaster_name', ''),
            'active_term' => $activeTerm ? $activeTerm->name : '2026/2027 • Semester Ganjil',
            'majors' => $majors->isNotEmpty() ? $majors : ['RPL'],
            'enable_demo_login' => Setting::get('enable_demo_login', 'true') === 'true',
        ]);
    }

    // ==========================================
    // 1. SETTINGS
    // ==========================================

    public function getSettings(Request $request)
    {
        $this->checkSuperadmin($request);

        $settings = Setting::all()->pluck('value', 'key');
        return response()->json($settings);
    }

    public function updateSettings(Request $request)
    {
        $this->checkSuperadmin($request);

        $data = $request->except(['_token']);

        foreach ($data as $key => $val) {
            Setting::set($key, is_array($val) ? json_encode($val) : (string)$val);
        }

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Memperbarui konfigurasi sistem sekolah',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Pengaturan sistem berhasil disimpan',
            'settings' => Setting::all()->pluck('value', 'key'),
        ]);
    }

    // ==========================================
    // 2. SYSTEM HEALTH
    // ==========================================

    public function getHealth(Request $request)
    {
        $this->checkSuperadmin($request);

        $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
        $token = env('WA_GATEWAY_TOKEN', 'rapiuw-admin-secret-2026');
        $waStatus = 'disconnected';
        $waDetails = null;

        try {
            $resp = Http::timeout(3)->withHeaders([
                'Authorization' => "Bearer {$token}",
                'x-admin-token' => $token,
            ])->get("{$baseUrl}/api/device/status");
            if ($resp->successful()) {
                $json = $resp->json();
                $data = $json['data'] ?? $json;
                $waStatus = (($data['status'] ?? '') === 'connected' || ($json['connected'] ?? false) === true) ? 'connected' : 'disconnected';
                $waDetails = $json;
            }
        } catch (\Exception $e) {
            $waStatus = 'offline';
        }

        $sqlitePath = database_path('database.sqlite');
        $dbSize = file_exists($sqlitePath) ? round(filesize($sqlitePath) / 1024, 2) . ' KB' : 'N/A';

        return response()->json([
            'db_connection' => config('database.default'),
            'db_size' => $dbSize,
            'db_status' => 'ONLINE',
            'wa_gateway_url' => $baseUrl,
            'wa_status' => $waStatus,
            'wa_details' => $waDetails,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_time' => Carbon::now()->toIso8601String(),
            'total_users' => User::count(),
            'total_audit_logs' => AuditLog::count(),
            'school_name' => Setting::get('school_name', 'SMK Negeri 1 Digital'),
        ]);
    }

    // ==========================================
    // 3. BROADCASTS & ANNOUNCEMENTS
    // ==========================================

    public function getBroadcasts(Request $request)
    {
        $this->checkSuperadmin($request);

        $broadcasts = Broadcast::with('sender')->latest()->take(30)->get();
        return response()->json($broadcasts);
    }

    public function sendBroadcast(Request $request)
    {
        $this->checkSuperadmin($request);

        $request->validate([
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'target_role' => 'required|string', // ALL, SISWA, SEKRETARIS, GURU_PENGAJAR, GURU_PEMBIMBING
            'channel' => 'required|in:IN_APP,WHATSAPP,ALL',
        ]);

        $query = User::where('is_active', true);
        if ($request->target_role !== 'ALL') {
            $query->where('role', $request->target_role);
        }
        $recipients = $query->get();
        $count = $recipients->count();

        // 1. Send In-App Notifications
        if (in_array($request->channel, ['IN_APP', 'ALL'])) {
            foreach ($recipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'type' => 'PENGUMUMAN',
                    'title' => '📢 ' . $request->title,
                    'body' => $request->message,
                ]);
            }
        }

        // 2. Send WhatsApp Messages if selected
        $waSentCount = 0;
        if (in_array($request->channel, ['WHATSAPP', 'ALL'])) {
            $baseUrl = rtrim(env('WA_GATEWAY_BASE_URL', 'http://127.0.0.1:3000'), '/');
            $waToken = env('WA_GATEWAY_TOKEN', 'rapiuw-admin-secret-2026');

            foreach ($recipients as $recipient) {
                if ($recipient->phone) {
                    try {
                        Http::timeout(5)->withHeaders([
                            'Authorization' => "Bearer {$waToken}",
                        ])->post("{$baseUrl}/api/send", [
                            'phone' => $recipient->phone,
                            'message' => "*📢 [PENGUMUMAN SEKOLAH]*\n\n*{$request->title}*\n\n{$request->message}\n\n_Pesan otomatis Sistem Jurnal Digital_",
                        ]);
                        $waSentCount++;
                    } catch (\Exception $e) {
                        // ignore failures in bulk wa
                    }
                }
            }
        }

        // Log broadcast
        $broadcast = Broadcast::create([
            'sender_id' => $request->user()->id,
            'target_role' => $request->target_role,
            'title' => $request->title,
            'message' => $request->message,
            'channel' => $request->channel,
            'recipients_count' => $count,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => "Mengirim broadcast '{$request->title}' kepada {$count} penerima ({$request->channel})",
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => "Pengumuman berhasil disiarkan kepada {$count} penerima" . ($waSentCount > 0 ? " ({$waSentCount} via WhatsApp)" : ""),
            'broadcast' => $broadcast,
            'recipients_count' => $count,
        ]);
    }

    // ==========================================
    // 4. BACKUP / EXPORT
    // ==========================================

    public function downloadBackup(Request $request)
    {
        $this->checkSuperadmin($request);

        $data = [
            'app' => 'Jurnal Kelas Digital',
            'exported_at' => Carbon::now()->toIso8601String(),
            'users' => User::all(),
            'classes' => \App\Models\SchoolClass::all(),
            'subjects' => \App\Models\Subject::all(),
            'schedules' => \App\Models\Schedule::all(),
            'terms' => \App\Models\Term::all(),
            'settings' => Setting::all(),
            'audit_logs' => AuditLog::latest()->take(200)->get(),
        ];

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Mengunduh snapshot cadangan (backup) data sistem',
            'ip_address' => $request->ip(),
        ]);

        return response()->json($data);
    }
}
