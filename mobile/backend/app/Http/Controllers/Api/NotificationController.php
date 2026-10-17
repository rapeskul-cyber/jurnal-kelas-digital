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
        $filter = $request->input('filter'); // ALL, UNREAD, JURNAL, SURAT_IZIN

        $query = Notification::where('user_id', $user->id)->latest();

        if ($filter === 'UNREAD') {
            $query->whereNull('read_at');
        } elseif ($filter === 'JURNAL') {
            $query->where('type', 'JURNAL');
        } elseif ($filter === 'SURAT_IZIN') {
            $query->where('type', 'SURAT_IZIN');
        }

        $notifications = $query->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'time_ago' => Carbon::parse($n->created_at)->diffForHumans(),
                'is_read' => $n->read_at !== null,
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
        $query = AuditLog::with('user')->latest();

        // If not GURU_PEMBIMBING, only see own logs
        if ($user->role !== 'GURU_PEMBIMBING') {
            $query->where('user_id', $user->id);
        }

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
}
