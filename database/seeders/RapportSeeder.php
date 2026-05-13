<?php

namespace Database\Seeders;

use App\Models\Rapport;
use App\Models\User;
use Illuminate\Database\Seeder;

class RapportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'administrateur')->get();

        if ($users->isEmpty()) {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            Rapport::factory()->count(2)->create([
                'user_id' => $user->id
            ]);
        }
    }
}
