<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRating extends Model
{
    protected $fillable = ['student_id', 'teacher_id', 'rating', 'comment'];

    protected static function booted()
    {
        static::creating(function ($rating) {
            $today = today();
            $alreadyExists = static::where('student_id', $rating->student_id)
                ->where('teacher_id', $rating->teacher_id)
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadyExists) {
                throw new \Exception('Student already rated teacher today.');
            }
        });
    }
}