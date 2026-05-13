<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\ActivityLog;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CommandeDetailController extends Controller
{
    /**
     * GET /api/commande-details
     * List all commande details
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'commande_id' => ['sometimes', 'integer', 'exists:commandes,id'],
            'product_id'  => ['sometimes', 'integer', 'exists:products,id'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $details = CommandeDetail::query()

            ->with([
                'commande:id,dateCommande,statut',
                'product:id,nom'
            ])

            ->when(
                $request->filled('commande_id'),
                fn($q) => $q->where('commande_id', $request->commande_id)
            )

            ->when(
                $request->filled('product_id'),
                fn($q) => $q->where('product_id', $request->product_id)
            )

            ->latest()

            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,

            'data' => $details->items(),

            'meta' => [
                'current_page' => $details->currentPage(),
                'last_page' => $details->lastPage(),
                'total' => $details->total(),
                'per_page' => $details->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/commande-details
     * Create one detail line
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'commande_id' => [
                'required',
                'integer',
                'exists:commandes,id'
            ],

            'product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1'
            ],

            'unit_price_ht' => [
                'required',
                'numeric',
                'min:0'
            ],

            'tax_rate' => [
                'required',
                'numeric',
                'min:0'
            ],
        ]);

        try {

            return DB::transaction(function () use ($validated) {

                $subtotalHt =
                    $validated['quantity']
                    * $validated['unit_price_ht'];

                $taxAmount =
                    $subtotalHt
                    * ($validated['tax_rate'] / 100);

                $subtotalTtc =
                    $subtotalHt
                    + $taxAmount;

                // ✅ Create detail
                $detail = CommandeDetail::create([

                    'commande_id' => $validated['commande_id'],

                    'product_id' => $validated['product_id'],

                    'quantity' => $validated['quantity'],

                    'unit_price_ht' => $validated['unit_price_ht'],

                    'tax_rate' => $validated['tax_rate'],

                    'subtotal_ht' => $subtotalHt,

                    'tax_amount' => $taxAmount,

                    'subtotal_ttc' => $subtotalTtc,
                ]);

                // ✅ Recalculate commande totals
                $commande = Commande::findOrFail(
                    $validated['commande_id']
                );

                $totalHt = $commande->details()->sum('subtotal_ht');

                $totalTax = $commande->details()->sum('tax_amount');

                $totalTtc = $commande->details()->sum('subtotal_ttc');

                $commande->update([
                    'total_ht' => $totalHt,
                    'total_tax' => $totalTax,
                    'total_ttc' => $totalTtc,
                ]);

                ActivityLog::log(
                    "Ajout d'une ligne à la commande ID #{$commande->id}"
                );

                return response()->json([
                    'success' => true,

                    'message' => 'Ligne de commande créée avec succès.',

                    'data' => $detail->load([
                        'commande:id,dateCommande',
                        'product:id,nom'
                    ]),
                ], 201);
            });

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,

                'message' => 'Erreur lors de la création.',

                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/commande-details/{id}
     */
    public function show(int $id): JsonResponse
    {
        $detail = CommandeDetail::with([
            'commande:id,dateCommande,statut',
            'product:id,nom'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    /**
     * PUT /api/commande-details/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $detail = CommandeDetail::findOrFail($id);

        $validated = $request->validate([

            'quantity' => [
                'sometimes',
                'integer',
                'min:1'
            ],

            'unit_price_ht' => [
                'sometimes',
                'numeric',
                'min:0'
            ],

            'tax_rate' => [
                'sometimes',
                'numeric',
                'min:0'
            ],
        ]);

        try {

            return DB::transaction(function () use (
                $detail,
                $validated
            ) {

                $quantity =
                    $validated['quantity']
                    ?? $detail->quantity;

                $unitPrice =
                    $validated['unit_price_ht']
                    ?? $detail->unit_price_ht;

                $taxRate =
                    $validated['tax_rate']
                    ?? $detail->tax_rate;

                $subtotalHt = $quantity * $unitPrice;

                $taxAmount =
                    $subtotalHt
                    * ($taxRate / 100);

                $subtotalTtc =
                    $subtotalHt
                    + $taxAmount;

                $detail->update([

                    'quantity' => $quantity,

                    'unit_price_ht' => $unitPrice,

                    'tax_rate' => $taxRate,

                    'subtotal_ht' => $subtotalHt,

                    'tax_amount' => $taxAmount,

                    'subtotal_ttc' => $subtotalTtc,
                ]);

                // ✅ Recalculate commande totals
                $commande = $detail->commande;

                $commande->update([
                    'total_ht' => $commande->details()->sum('subtotal_ht'),

                    'total_tax' => $commande->details()->sum('tax_amount'),

                    'total_ttc' => $commande->details()->sum('subtotal_ttc'),
                ]);

                ActivityLog::log(
                    "Modification d'une ligne de commande ID #{$detail->id}"
                );

                return response()->json([
                    'success' => true,

                    'message' => 'Ligne mise à jour avec succès.',

                    'data' => $detail->fresh()->load([
                        'commande:id,dateCommande',
                        'product:id,nom'
                    ]),
                ]);
            });

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,

                'message' => 'Erreur lors de la mise à jour.',

                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/commande-details/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        try {

            return DB::transaction(function () use ($id) {

                $detail = CommandeDetail::findOrFail($id);

                $commande = $detail->commande;

                $detail->delete();

                // ✅ Recalculate totals
                $commande->update([
                    'total_ht' => $commande->details()->sum('subtotal_ht'),

                    'total_tax' => $commande->details()->sum('tax_amount'),

                    'total_ttc' => $commande->details()->sum('subtotal_ttc'),
                ]);

                ActivityLog::log(
                    "Suppression d'une ligne de commande ID #{$id}"
                );

                return response()->json([
                    'success' => true,

                    'message' => 'Ligne supprimée avec succès.',
                ]);
            });

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,

                'message' => 'Erreur lors de la suppression.',

                'error' => $e->getMessage()
            ], 500);
        }
    }
}