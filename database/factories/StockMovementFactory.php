<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['entrée', 'sortie', 'ajustement']),
            'quantite' => fake()->numberBetween(1, 100),
            'dateheure' => fake()->dateTimeBetween('-1 month', 'now'),
            'localisation' => fake()->randomElement(['Entrepôt A', 'Entrepôt B', 'Magasin Central']),
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
        ];
    }
}
