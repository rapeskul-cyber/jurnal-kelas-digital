<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'class_id', 'date', 'status'];

    protected $uniqueNames = ['student_id', 'subject_id', 'date'];
}