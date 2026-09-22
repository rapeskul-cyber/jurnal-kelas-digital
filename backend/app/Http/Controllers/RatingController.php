<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherRating;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($request->user()->id === $validated['teacher_id']) {
            return response()->json(['error' => 'Cannot rate yourself'], 400);
        }

        try {
            $rating = TeacherRating::create([
                'student_id' => $request->user()->id,
                'teacher_id' => $validated['teacher_id'],
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);
            return response()->json($rating, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function show(string $teacherId): JsonResponse
    {
        $ratings = TeacherRating::with('student')
            ->where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->get();

        $average = $ratings->avg('rating') ?: 0;

        return response()->json([
            'average' => round($average, 1),
            'total' => $ratings->count(),
            'ratings' => $ratings,
        ]);
    }
}