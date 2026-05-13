<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Commande;
use App\Models\Product;
use App\Models\CommandeDetail;

class CommandeDetailSeeder extends Seeder
{
    public function run(): void
    {
        $commandes = Commande::all();

        $products = Product::all();

        if ($commandes->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($commandes as $commande) {

            // Random products per order
            $selectedProducts = $products->random(rand(1, 4));

            foreach ($selectedProducts as $product) {

                $quantity = rand(1, 5);

                $unitPriceHt = rand(100, 1000);

                $taxRate = 20;

                $subtotalHt = $quantity * $unitPriceHt;

                $taxAmount = $subtotalHt * ($taxRate / 100);

                $subtotalTtc = $subtotalHt + $taxAmount;

                CommandeDetail::create([
                    'commande_id' => $commande->id,

                    'product_id' => $product->id,

                    'quantity' => $quantity,

                    'unit_price_ht' => $unitPriceHt,

                    'tax_rate' => $taxRate,

                    'subtotal_ht' => $subtotalHt,

                    'tax_amount' => $taxAmount,

                    'subtotal_ttc' => $subtotalTtc,
                ]);
            }
        }
    }
}