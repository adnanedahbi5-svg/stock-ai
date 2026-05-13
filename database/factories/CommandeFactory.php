<?php

namespace Database\Factories;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommandeFactory extends Factory
{
    protected $model = Commande::class;

    public function definition(): array
    {
        $totalHt = fake()->randomFloat(2, 100, 5000);
        $totalTax = $totalHt * 0.20; // Example 20% tax
        $totalTtc = $totalHt + $totalTax;

        return [
            'dateCommande' => fake()
                ->dateTimeBetween('-1 month', 'now')
                ->format('Y-m-d'),

            'statut' => fake()->randomElement([
                'en_attente',
                'recue',
                'annulee'
            ]),

            'fournisseur_id' => Fournisseur::factory(),

            'user_id' => User::factory(),

            // ✅ NEW TOTALS
            'total_ht' => $totalHt,
            'total_tax' => $totalTax,
            'total_ttc' => $totalTtc,
        ];
    }
}