<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        if ($products->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            StockMovement::factory()->count(5)->create([
                'product_id' => $product->id,
                'user_id' => $users->random()->id
            ]);
        }
    }
}
