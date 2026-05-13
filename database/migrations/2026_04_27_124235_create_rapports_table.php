<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // ex: "Rapport Mensuel Avril 2026"
            $table->dateTime('dateCreation');
            $table->string('type'); // ex: "PDF", "Excel", "Stock", "Ventes"
            // Le chemin vers le fichier généré sur le serveur (storage)
            $table->string('file_path')->nullable(); 
            // Qui a généré le rapport
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
