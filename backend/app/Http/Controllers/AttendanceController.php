<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class AttendanceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:hadir,izin,sakit,alpha',
        ]);

        try {
            $attendance = Attendance::create($validated);
            return response()->json($attendance, 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json(['error' => 'Kehadiran sudah diinput hari ini'], 409);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['student', 'subject', 'class']);

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('month')) {
            $query->whereYear('date', date('Y'))
                  ->whereMonth('date', $request->month);
        }

        return response()->json($query->orderBy('date', 'desc')->get());
    }
}