<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rapport extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'dateCreation',
        'type',
        'file_path',
        'user_id'
    ];

    protected $casts = [
        'dateCreation' => 'datetime',
        'user_id' => 'integer',
    ];

    /**
     * L'utilisateur (Admin/Gestionnaire) qui a généré ce rapport
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
