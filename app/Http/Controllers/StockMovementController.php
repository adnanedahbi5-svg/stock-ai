<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class StockMovementController extends Controller
{
    /**
     * GET /api/movements
     * List all stock movements with product info.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type'       => ['sometimes', 'in:Entrée,Sortie'],
            'product_id' => ['sometimes', 'integer', 'exists:products,id'],
            'search'      => ['sometimes', 'string', 'max:255'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $movements = StockMovement::query()
            ->with(['product:id,nom', 'user:id,name'])
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('localisation', 'like', '%' . $request->search . '%')
                        ->orWhereHas('product', function ($p) use ($request) {
                            $p->where('nom', 'like', '%' . $request->search . '%');
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $movements->items(),
            'meta'    => [
                'current_page' => $movements->currentPage(),
                'last_page'    => $movements->lastPage(),
                'total'        => $movements->total(),
                'per_page'     => $movements->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/movements
     * Record a new stock movement (In/Out).
     */
    public function store(Request $request)
    {
        // 1. Validation of input data
        $validated = $request->validate([
            'product_id'   => 'required|exists:products,id',
            'type'         => 'required|in:Entrée,Sortie',
            'quantite'     => 'required|integer|min:1',
            'localisation' => 'nullable|string|max:255',
        ]);

        try {
            // Use a transaction to ensure both records update together
            return DB::transaction(function () use ($validated) {
                $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

                // 2. Core Stock Logic
                if ($validated['type'] === 'Sortie') {
                    // Check availability before decrementing
                    if ($product->quantiteStock < $validated['quantite']) {
                        return response()->json([
                            'message' => 'Stock insuffisant pour cette sortie.'
                        ], 422);
                    }
                    $product->decrement('quantiteStock', $validated['quantite']);
                } else {
                    $product->increment('quantiteStock', $validated['quantite']);
                }

                // 3. Create the Movement Record (The "History")
                $movement = StockMovement::create([
                    'type'         => $validated['type'],
                    'quantite'     => $validated['quantite'],
                    'dateheure'    => now(),
                    'localisation' => $validated['localisation'] ?? 'Entrepôt principal',
                    'product_id'   => $product->id,
                    'user_id'      => auth()->id(), // Authenticated user
                ]);

                ActivityLog::log("Mouvement de stock: {$validated['type']} de {$validated['quantite']} pour le produit: {$product->nom}");

                // Return the movement and the updated product info for Pinia
                return response()->json([
                    'message' => 'Mouvement enregistré avec succès',
                    'movement' => $movement,
                    'new_stock' => $product->quantiteStock
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors du traitement'], 500);
        }
    }
}