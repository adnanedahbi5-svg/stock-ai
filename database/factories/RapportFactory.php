<?php

namespace Database\Factories;

use App\Models\Rapport;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RapportFactory extends Factory
{
    protected $model = Rapport::class;

    public function definition(): array
    {
        return [
            'nom' => 'Rapport ' . fake()->monthName() . ' ' . fake()->year(),
            'dateCreation' => fake()->dateTimeBetween('-1 month', 'now'),
            'type' => fake()->randomElement(['PDF', 'Excel', 'Stock', 'Ventes']),
            'file_path' => 'reports/' . fake()->uuid() . '.pdf',
            'user_id' => User::factory(),
        ];
    }
}
