<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'action' => fake()->randomElement([
                'Création produit', 
                'Suppression utilisateur', 
                'Modification stock', 
                'Génération rapport'
            ]),
            'dateHeure' => fake()->dateTimeBetween('-1 week', 'now'),
            'user_id' => User::factory(),
        ];
    }
}
