<?php

namespace Database\Seeders;

use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommandeSeeder extends Seeder
{
    public function run(): void
    {
        $fournisseurs = Fournisseur::all();
        $users = User::all();

        if ($fournisseurs->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($fournisseurs as $fournisseur) {

            Commande::factory()
                ->count(3)
                ->create([
                    'fournisseur_id' => $fournisseur->id,
                    'user_id' => $users->random()->id,
                ]);
        }
    }
}