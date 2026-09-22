<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JournalEntry;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'version' => '2.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found. Please register first.',
                'status' => 'not_registered'
            ], 404);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function summary(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_journals' => JournalEntry::count(),
            'total_attendance' => Attendance::count(),
            'recent_journals' => JournalEntry::with('author')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ]);
    }
}