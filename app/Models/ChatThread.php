<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
    ];

    /**
     * Thread owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Messages inside thread
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'thread_id')
            ->orderBy('created_at');
    }
}