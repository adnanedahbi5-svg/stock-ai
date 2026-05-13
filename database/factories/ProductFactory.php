<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'nom' => fake()->words(3, true),
            'codeBarre' => fake()->unique()->ean13(),
            'quantiteStock' => fake()->numberBetween(0, 1000),
            'seuilAlerte' => fake()->numberBetween(5, 50),
            'category_id' => Category::factory(),
        ];
    }
}
