<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassModel extends Model
{
    protected $table = 'classes';
    protected $fillable = ['name', 'academic_year'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'class_user');
    }

    public function journals()
    {
        return $this->hasMany(JournalEntry::class);
    }
}