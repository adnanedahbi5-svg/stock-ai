<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commande_details', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('commande_id')
                ->constrained('commandes')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // Product order info
            $table->integer('quantity');

            // Pricing
            $table->decimal('unit_price_ht', 10, 2);

            // Tax percentage
            $table->decimal('tax_rate', 5, 2)->default(20);

            // Calculated values
            $table->decimal('subtotal_ht', 10, 2);

            $table->decimal('tax_amount', 10, 2);

            $table->decimal('subtotal_ttc', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commande_details');
    }
};