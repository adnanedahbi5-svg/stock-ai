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
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();

            $table->date('dateCommande');
            $table->string('statut')->default('en_attente');

            // Relations
            $table->foreignId('fournisseur_id')
                ->constrained('fournisseurs')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // ✅ NEW: totals (calculated from CommandeDetail)
            $table->decimal('total_ht', 10, 2)->default(0);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('total_ttc', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};