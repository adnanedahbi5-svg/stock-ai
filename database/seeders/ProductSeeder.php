<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        if ($categories->isEmpty()) {
            $categories = Category::factory()->count(3)->create();
        }

        foreach ($categories as $category) {
            Product::factory()->count(10)->create([
                'category_id' => $category->id
            ]);
        }
    }
}
