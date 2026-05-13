<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Informatique', 'Papeterie', 'Mobilier', 'Électronique', 'Consommables'];

        foreach ($categories as $cat) {
            Category::factory()->create(['nom' => $cat]);
        }
    }
}
