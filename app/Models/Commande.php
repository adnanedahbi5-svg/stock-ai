<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commande extends Model
{
    use HasFactory;

    protected $fillable = [
        'dateCommande',
        'statut',
        'fournisseur_id',
        'user_id',

        // ✅ NEW TOTALS
        'total_ht',
        'total_tax',
        'total_ttc',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'dateCommande' => 'date',

        'fournisseur_id' => 'integer',
        'user_id' => 'integer',

        // ✅ TOTALS
        'total_ht' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_ttc' => 'decimal:2',
    ];

    /**
     * Une commande appartient à un fournisseur
     */
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    /**
     * L'utilisateur qui a créé la commande
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Les lignes de la commande
     */
    public function details(): HasMany
    {
        return $this->hasMany(CommandeDetail::class);
    }
}