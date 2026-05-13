<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:create-admin
                            {--name=         : Full name of the admin}
                            {--email=        : Email address of the admin}
                            {--password=     : Password (min 8 chars, letters & numbers)}
                            {--secteur=      : Secteur (optional)}
                            {--poste=        : Poste (optional)}
                            {--niveau_acces= : Niveau acces (optional)}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new administrator user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('=== Create New Administrator ===');
        $this->newLine();

        // Collect inputs — use options if provided, otherwise prompt interactively
        $name     = $this->option('name')         ?: $this->ask('Full name');
        $email    = $this->option('email')        ?: $this->ask('Email address');
        $password = $this->option('password')     ?: $this->secret('Password (min 8 chars, letters & numbers)');
        $secteur  = $this->option('secteur')      ?: $this->ask('Secteur (leave blank to skip)', null);
        $poste    = $this->option('poste')        ?: $this->ask('Poste (leave blank to skip)', null);
        $niveau   = $this->option('niveau_acces') ?: $this->ask("Niveau d'acces (leave blank to skip)", null);

        // Validate all inputs
        $validator = Validator::make([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
        ], [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/'],
        ], [
            'email.unique'   => 'A user with this email already exists.',
            'password.regex' => 'Password must contain at least one letter and one number.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error("  x {$error}");
            }
            return self::FAILURE;
        }

        // Confirm before creating
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name',         $name],
                ['Email',        $email],
                ['Role',         'administrateur'],
                ['Secteur',      $secteur ?? '-'],
                ['Poste',        $poste   ?? '-'],
                ['Niveau acces', $niveau  ?? '-'],
            ]
        );

        if (! $this->confirm('Create this administrator?', true)) {
            $this->warn('Cancelled. No user was created.');
            return self::FAILURE;
        }

        // Create the user
        $user = User::create([
            'name'         => $name,
            'email'        => $email,
            'password'     => Hash::make($password),
            'role'         => 'administrateur',
            'secteur'      => $secteur ?: null,
            'poste'        => $poste   ?: null,
            'niveau_acces' => $niveau  ?: null,
        ]);

        $this->newLine();
        $this->info("Administrator created successfully! (ID: {$user->id})");

        return self::SUCCESS;
    }
}