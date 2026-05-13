<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'dateGestion',
        'remarque',
        'product_id'
    ];

    protected $casts = [
        'dateGestion' => 'datetime',
        'product_id' => 'integer',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Le stock concerne un produit spécifique
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
