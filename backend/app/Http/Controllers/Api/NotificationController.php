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

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id = null)
    {
        $user = $request->user();

        if ($id && $id !== 'all') {
            Notification::where('user_id', $user->id)->where('id', $id)->update(['read_at' => Carbon::now()]);
        } else {
            Notification::where('user_id', $user->id)->whereNull('read_at')->update(['read_at' => Carbon::now()]);
        }

        return response()->json(['message' => 'Notifikasi ditandai telah dibaca']);
    }

    public function auditLogs(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'GURU_PEMBIMBING') {
            return response()->json(['message' => 'Hanya Guru Pembimbing yang berhak mengakses log audit sistem.'], 403);
        }

        $query = AuditLog::with('user')->latest();
        $logs = $query->take(50)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'time' => Carbon::parse($log->created_at)->translatedFormat('H:i') . ' WIB',
                'user' => $log->user ? $log->user->name . ' (' . $log->user->role . ')' : 'Sistem',
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
