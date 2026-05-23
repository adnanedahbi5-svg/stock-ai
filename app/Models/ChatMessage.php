<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'role',
        'content',
        'is_error',
    ];

    /**
     * Parent thread
     */
    public function thread()
    {
        return $this->belongsTo(ChatThread::class);
    }
}