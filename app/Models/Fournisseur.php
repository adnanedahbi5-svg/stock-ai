<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Fournisseur extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'contact',
        'adresse'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Un fournisseur peut être lié à plusieurs commandes
     */
    public function commandes(): HasMany
    {
        return $this->hasMany(Commande::class);
    }
}
