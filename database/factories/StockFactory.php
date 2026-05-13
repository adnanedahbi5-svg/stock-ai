<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockFactory extends Factory
{
    protected $model = Stock::class;

    public function definition(): array
    {
        return [
            'dateGestion' => fake()->dateTimeBetween('-1 month', 'now'),
            'remarque' => fake()->sentence(),
            'product_id' => Product::factory(),
        ];
    }
}
