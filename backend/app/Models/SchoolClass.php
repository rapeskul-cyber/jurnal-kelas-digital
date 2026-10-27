<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'major',
        'advisor_id',
        'secretary_id',
    ];

    public function advisor()
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    public function secretary()
    {
        return $this->belongsTo(User::class, 'secretary_id');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->whereIn('role', ['SISWA', 'SEKRETARIS']);
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'class_id', 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_id');
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class, 'class_id');
    }

    public function classJournals()
    {
        return $this->hasMany(ClassJournal::class, 'class_id');
    }
}
