<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class FournisseurController extends Controller
{
    /**
     * GET /api/fournisseurs
     * List all suppliers with search and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'trashed'  => ['sometimes', 'boolean'],
        ]);

        $fournisseurs = Fournisseur::query()
            ->when($request->boolean('trashed'), fn($q) => $q->onlyTrashed())
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('contact', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse', 'like', '%' . $request->search . '%');
            })
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $fournisseurs->items(),
            'meta'    => [
                'current_page' => $fournisseurs->currentPage(),
                'last_page'    => $fournisseurs->lastPage(),
                'total'        => $fournisseurs->total(),
                'per_page'     => $fournisseurs->perPage(),
            ]
        ]);
    }
    // public function select(){
    //     // Return a simple list for dropdowns
    //     return response()->json([
    //         'success' => true,
    //         'data' => Fournisseur::select('id', 'nom')->get()
    //     ]);
    // }

    /**
     * POST /api/fournisseurs
     * Create a new supplier.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'     => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:500'],
        ]);

        $fournisseur = Fournisseur::create($validated);

        ActivityLog::log("Création du fournisseur: {$fournisseur->nom}");

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur created successfully.',
            'data'    => $fournisseur,
        ], 201);
    }

    /**
     * GET /api/fournisseurs/{id}
     * Return a single supplier.
     */
    public function show(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $fournisseur,
        ]);
    }

    /**
     * PUT /api/fournisseurs/{id}
     * Update an existing supplier.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);

        $validated = $request->validate([
            'nom'     => ['sometimes', 'string', 'max:255'],
            'contact' => ['sometimes', 'string', 'max:255'],
            'adresse' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $fournisseur->update($validated);

        ActivityLog::log("Mise à jour du fournisseur: {$fournisseur->nom}");

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur updated successfully.',
            'data'    => $fournisseur->fresh(),
        ]);
    }

    /**
     * DELETE /api/fournisseurs/{id}
     * Soft delete a supplier.
     */
    public function softDelete(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::findOrFail($id);
        $fournisseur->delete();

        ActivityLog::log("Suppression (soft) du fournisseur ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Fournisseur #{$id} has been soft deleted.",
        ]);
    }

    /**
     * DELETE /api/fournisseurs/{id}/force
     * Permanently delete a supplier.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::withTrashed()->findOrFail($id);
        $fournisseur->forceDelete();

        ActivityLog::log("Suppression permanente du fournisseur ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Fournisseur #{$id} has been permanently deleted.",
        ]);
    }

    /**
     * PATCH /api/fournisseurs/{id}/restore
     * Restore a soft-deleted supplier.
     */
    public function restore(int $id): JsonResponse
    {
        $fournisseur = Fournisseur::withTrashed()->findOrFail($id);
        $fournisseur->restore();

        ActivityLog::log("Restauration du fournisseur ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Fournisseur #{$id} has been restored.",
            'data'    => $fournisseur->fresh(),
        ]);
    }
}