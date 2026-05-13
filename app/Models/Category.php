<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nom'];

    // On cache les timestamps pour l'API si tu n'en as pas besoin côté Vue.js
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Une catégorie possède plusieurs produits
     */
    public function produits(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
