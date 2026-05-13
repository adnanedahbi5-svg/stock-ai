<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\CommandeDetail;
use App\Models\Product;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    /**
     * GET /api/commandes
     * List all commandes with filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'fournisseur_id' => ['sometimes', 'integer', 'exists:fournisseurs,id'],
            'statut'         => ['sometimes', Rule::in(['en_attente', 'recue', 'annulee'])],
            'per_page'       => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $commandes = Commande::query()
            ->with([
                'fournisseur:id,nom',
                'user:id,name',
                'details.product:id,nom'
            ])

            ->when(
                $request->filled('fournisseur_id'),
                fn($q) => $q->where('fournisseur_id', $request->fournisseur_id)
            )

            ->when(
                $request->filled('statut'),
                fn($q) => $q->where('statut', $request->statut)
            )

            ->orderBy('dateCommande', 'desc')

            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,

            'data' => $commandes->items(),

            'meta' => [
                'current_page' => $commandes->currentPage(),
                'last_page'    => $commandes->lastPage(),
                'total'        => $commandes->total(),
                'per_page'     => $commandes->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/commandes
     * Create commande with details.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([

            'dateCommande' => ['required', 'date'],

            'statut' => [
                'sometimes',
                Rule::in(['en_attente', 'recue', 'annulee'])
            ],

            'fournisseur_id' => [
                'required',
                'integer',
                'exists:fournisseurs,id'
            ],

            'user_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],

            // ✅ Commande details
            'details' => ['required', 'array', 'min:1'],

            'details.*.product_id' => [
                'required',
                'integer',
                'exists:products,id'
            ],

            'details.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],

            'details.*.unit_price_ht' => [
                'required',
                'numeric',
                'min:0'
            ],

            'details.*.tax_rate' => [
                'required',
                'numeric',
                'min:0'
            ],
        ]);

        try {

            return DB::transaction(function () use ($validated) {

                // ✅ Create commande first
                $commande = Commande::create([
                    'dateCommande' => $validated['dateCommande'],

                    'statut' => $validated['statut'] ?? 'en_attente',

                    'fournisseur_id' => $validated['fournisseur_id'],

                    'user_id' => $validated['user_id'],

                    // Temporary totals
                    'total_ht' => 0,
                    'total_tax' => 0,
                    'total_ttc' => 0,
                ]);

                $totalHt = 0;
                $totalTax = 0;
                $totalTtc = 0;

                // ✅ Create details
                foreach ($validated['details'] as $detail) {

                    $subtotalHt =
                        $detail['quantity']
                        * $detail['unit_price_ht'];

                    $taxAmount =
                        $subtotalHt
                        * ($detail['tax_rate'] / 100);

                    $subtotalTtc =
                        $subtotalHt
                        + $taxAmount;

                    CommandeDetail::create([

                        'commande_id' => $commande->id,

                        'product_id' => $detail['product_id'],

                        'quantity' => $detail['quantity'],

                        'unit_price_ht' => $detail['unit_price_ht'],

                        'tax_rate' => $detail['tax_rate'],

                        'subtotal_ht' => $subtotalHt,

                        'tax_amount' => $taxAmount,

                        'subtotal_ttc' => $subtotalTtc,
                    ]);

                    // ✅ Accumulate totals
                    $totalHt += $subtotalHt;

                    $totalTax += $taxAmount;

                    $totalTtc += $subtotalTtc;
                }

                // ✅ Update final totals
                $commande->update([
                    'total_ht' => $totalHt,
                    'total_tax' => $totalTax,
                    'total_ttc' => $totalTtc,
                ]);

                // ✅ If commande is received, add quantities to product stock
                if (($validated['statut'] ?? 'en_attente') === 'recue') {
                    foreach ($validated['details'] as $detail) {
                        Product::where('id', $detail['product_id'])
                            ->increment('quantiteStock', $detail['quantity']);
                    }
                    ActivityLog::log(
                        "Stock mis à jour suite à la réception de la commande ID #{$commande->id}"
                    );
                }

                ActivityLog::log(
                    "Création de la commande ID #{$commande->id}"
                );

                return response()->json([
                    'success' => true,

                    'message' => 'Commande créée avec succès.',

                    'data' => $commande->fresh()->load([
                        'fournisseur:id,nom',
                        'user:id,name',
                        'details.product:id,nom'
                    ]),
                ], 201);
            });

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/commandes/{id}
     * Show one commande.
     */
    public function show(int $id): JsonResponse
    {
        $commande = Commande::with([
            'fournisseur:id,nom',
            'user:id,name',
            'details.product:id,nom'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $commande,
        ]);
    }

    public function downloadPdf($id){
        $commande = Commande::with([
            'fournisseur',
            'user',
            'details.product'
        ])->findOrFail($id);
        

        $pdf = Pdf::loadView(
            'pdf.commande',
            compact('commande')
        );

        return $pdf->download(
            'bon_commande_' . $commande->id . '.pdf'
        );
    }

    /**
     * PUT /api/commandes/{id}
     * Update basic commande info.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $commande = Commande::findOrFail($id);

        $validated = $request->validate([

            'dateCommande' => ['sometimes', 'date'],

            'statut' => [
                'sometimes',
                Rule::in(['en_attente', 'recue', 'annulee'])
            ],

            'fournisseur_id' => [
                'sometimes',
                'integer',
                'exists:fournisseurs,id'
            ],

            'user_id' => [
                'sometimes',
                'integer',
                'exists:users,id'
            ],
        ]);

        $previousStatut = $commande->statut;

        $commande->update($validated);

        // ✅ If status changed TO 'recue', add quantities to product stock
        if (
            isset($validated['statut'])
            && $validated['statut'] === 'recue'
            && $previousStatut !== 'recue'
        ) {
            $commande->load('details');
            foreach ($commande->details as $detail) {
                Product::where('id', $detail->product_id)
                    ->increment('quantiteStock', $detail->quantity);
            }
            ActivityLog::log(
                "Stock mis à jour suite à la réception de la commande ID #{$commande->id}"
            );
        }

        ActivityLog::log(
            "Mise à jour de la commande ID #{$commande->id}"
        );

        return response()->json([
            'success' => true,

            'message' => 'Commande mise à jour avec succès.',

            'data' => $commande->fresh()->load([
                'fournisseur:id,nom',
                'user:id,name',
                'details.product:id,nom'
            ]),
        ]);
    }

    /**
     * DELETE /api/commandes/{id}
     * Delete commande.
     */
    public function destroy(int $id): JsonResponse
    {
        $commande = Commande::findOrFail($id);

        $commande->delete();

        ActivityLog::log(
            "Suppression de la commande ID #{$id}"
        );

        return response()->json([
            'success' => true,

            'message' => "La commande #{$id} a été supprimée.",
        ]);
    }
}