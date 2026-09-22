<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;

class JournalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::with(['class', 'subject', 'author']);

        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('content', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->has('subject')) {
            $query->whereHas('subject', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->subject . '%');
            });
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        $journals = $query->orderBy('created_at', 'desc')->get();

        return response()->json($journals);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'entry_type' => 'required|in:catatan,pr,pengumuman,ekskul',
            'tags' => 'nullable|array',
        ]);

        $journal = JournalEntry::create([
            'class_id' => $validated['class_id'],
            'subject_id' => $validated['subject_id'] ?? null,
            'author_id' => $request->user()->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'entry_type' => $validated['entry_type'],
            'status' => 'draft',
            'tags' => $validated['tags'] ?? [],
        ]);

        return response()->json($journal->load(['author', 'subject']), 201);
    }
}