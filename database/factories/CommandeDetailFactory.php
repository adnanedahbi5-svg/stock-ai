<?php

namespace Database\Factories;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommandeDetailFactory extends Factory
{
    protected $model = CommandeDetail::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);

        $unitPriceHt = fake()->randomFloat(2, 50, 1000);

        $taxRate = 20;

        $subtotalHt = $quantity * $unitPriceHt;

        $taxAmount = $subtotalHt * ($taxRate / 100);

        $subtotalTtc = $subtotalHt + $taxAmount;

        return [
            'commande_id' => Commande::factory(),

            'product_id' => Product::factory(),

            'quantity' => $quantity,

            'unit_price_ht' => $unitPriceHt,

            'tax_rate' => $taxRate,

            'subtotal_ht' => $subtotalHt,

            'tax_amount' => $taxAmount,

            'subtotal_ttc' => $subtotalTtc,
        ];
    }
}