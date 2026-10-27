<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\AuditLog;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->input('filter', 'ALL');

        // Allow both PRESENSI and other notification types
        $query = Notification::where('user_id', $user->id)->latest();

        if ($filter === 'UNREAD') {
            $query->whereNull('read_at');
        } elseif ($filter === 'JURNAL') {
            $query->where('type', 'JURNAL');
        } elseif ($filter === 'SURAT_IZIN') {
            $query->where('type', 'SURAT_IZIN');
        } elseif ($filter === 'PRESENSI') {
            $query->where('type', 'PRESENSI');
        }

        $notifications = $query->get()->map(function ($n) {
            $isRead = !is_null($n->read_at);
            return [
                'id' => $n->id,
                'title' => $n->title,
                'body' => $n->body,
                'type' => $n->type,
                'created_at' => $n->created_at ? $n->created_at->toISOString() : null,
                'time' => $n->created_at ? $n->created_at->diffForHumans() : 'Baru saja',
                'read' => $isRead,
                'is_read' => $isRead,
            ];
        });

        $unreadCount = Notification::where('user_id', $user->id)->whereNull('read_at')->count();
        $unreadSuratIzinCount = Notification::where('user_id', $user->id)->where('type', 'SURAT_IZIN')->whereNull('read_at')->count();
        $unreadJurnalCount = Notification::where('user_id', $user->id)->where('type', 'JURNAL')->whereNull('read_at')->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'unread_surat_izin_count' => $unreadSuratIzinCount,
            'unread_jurnal_count' => $unreadJurnalCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id = null)
    {
        $user = $request->user();
        $type = $request->input('type') ?? $request->query('type');

        if ($id && is_numeric($id)) {
            Notification::where('user_id', $user->id)->where('id', $id)->update(['read_at' => Carbon::now()]);
        } else {
            $query = Notification::where('user_id', $user->id)->whereNull('read_at');
            if ($type && in_array(strtoupper($type), ['JURNAL', 'SURAT_IZIN', 'PRESENSI'])) {
                $query->where('type', strtoupper($type));
            }
            $query->update(['read_at' => Carbon::now()]);
        }

        return response()->json(['message' => 'Notifikasi ditandai telah dibaca']);
    }

    public function auditLogs(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role, ['GURU_PEMBIMBING', 'SUPERADMIN'])) {
            return response()->json(['message' => 'Hanya Wali Kelas dan Superadmin yang berhak mengakses log audit sistem.'], 403);
        }

        $search = $request->input('search');
        $query = AuditLog::with('user')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%");
                  });
            });
        }

        $limit = $request->input('limit', 100);
        $logs = $query->take($limit)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'time' => Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i') . ' WIB',
                'user' => $log->user ? $log->user->name . ' (' . $log->user->role . ')' : 'Sistem',
                'raw_role' => $log->user ? $log->user->role : 'SYSTEM',
                'action' => $log->action,
                'ip_address' => $log->ip_address ?? '127.0.0.1',
            ];
        });

        return response()->json($logs);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::where('user_id', $user->id)->where('id', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notifikasi tidak ditemukan'], 404);
        }
        $notification->delete();
        return response()->json(['message' => 'Notifikasi berhasil dihapus']);
    }

    public function clearAll(Request $request)
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->delete();
        return response()->json(['message' => 'Semua riwayat notifikasi berhasil dibersihkan']);
    }
}
