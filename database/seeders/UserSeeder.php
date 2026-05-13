<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create an admin user
        User::factory()->create([
            'name' => 'Adnane',
            'email' => 'adnane@gmail.com',
            'password' => Hash::make('Adnane123@'),
            'role' => 'administrateur',
        ]);

        // Create some random users
        User::factory()->count(10)->create();
    }
}
