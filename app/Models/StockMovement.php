<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'quantite',
        'dateheure',
        'localisation',
        'product_id', 
        'user_id',    
    ];

    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'dateheure' => 'datetime',
        'quantite' => 'integer',
    ];

    /**
     * Relation avec le produit
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relation avec l'utilisateur (celui qui a fait le mouvement)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}