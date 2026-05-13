<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action',
        'dateHeure',
        'user_id',
    ];

    protected $casts = [
        'dateHeure' => 'datetime',
        'user_id' => 'integer',
    ];

    /**
     * Récupérer l'utilisateur auteur de l'action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Static helper to create a log entry easily.
     */
    public static function log(string $action, ?int $userId = null): self
    {
        return self::create([
            'action' => $action,
            'dateHeure' => now(),
            'user_id' => $userId ?? auth()->id(),
        ]);
    }
}