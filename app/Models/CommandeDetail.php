<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'commande_id',
        'product_id',

        'quantity',

        'unit_price_ht',
        'tax_rate',

        'subtotal_ht',
        'tax_amount',
        'subtotal_ttc',
    ];

    protected $casts = [
        'commande_id' => 'integer',
        'product_id' => 'integer',

        'quantity' => 'integer',

        'unit_price_ht' => 'decimal:2',
        'tax_rate' => 'decimal:2',

        'subtotal_ht' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal_ttc' => 'decimal:2',
    ];

    /**
     * Une ligne appartient à une commande
     */
    public function commande(): BelongsTo
    {
        return $this->belongsTo(Commande::class);
    }

    /**
     * Une ligne appartient à un produit
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}