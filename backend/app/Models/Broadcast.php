<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'target_role',
        'title',
        'message',
        'channel',
        'recipients_count',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
