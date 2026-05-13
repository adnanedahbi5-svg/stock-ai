<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * List all products with filtering, search, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'search'      => ['sometimes', 'string', 'max:255'],
            'per_page'    => ['sometimes', 'integer', 'min:1', 'max:100'],
            'trashed'     => ['sometimes', 'boolean'],
        ]);

        $products = Product::query()
            ->with('category:id,nom') // Eager load category for the UI
            ->when($request->boolean('trashed'), fn($q) => $q->onlyTrashed())
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('codeBarre', 'like', '%' . $request->search . '%');
            }))
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $products->items(),
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/products
     * Create a new product.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom'           => ['required', 'string', 'max:255'],
            'codeBarre'     => ['required', 'string', 'max:255', 'unique:products,codeBarre'],
            'quantiteStock' => ['required', 'integer', 'min:0'],
            'seuilAlerte'   => ['required', 'integer', 'min:0'],
            'category_id'   => ['required', 'integer', 'exists:categories,id'],
        ]);

        $product = Product::create($validated);

        ActivityLog::log("Création du produit: {$product->nom} (Code: {$product->codeBarre})");

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully.',
            'data'    => $product->load('category:id,nom'),
        ], 201);
    }

    /**
     * GET /api/products/{id}
     * Return a single product.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with('category:id,nom')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }

    /**
     * PUT /api/products/{id}
     * Update an existing product.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'nom'           => ['sometimes', 'string', 'max:255'],
            'codeBarre'     => ['sometimes', 'string', 'max:255', Rule::unique('products', 'codeBarre')->ignore($product->id)],
            'quantiteStock' => ['sometimes', 'integer', 'min:0'],
            'seuilAlerte'   => ['sometimes', 'integer', 'min:0'],
            'category_id'   => ['sometimes', 'integer', 'exists:categories,id'],
        ]);

        $product->update($validated);

        ActivityLog::log("Mise à jour du produit: {$product->nom}");

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully.',
            'data'    => $product->fresh()->load('category:id,nom'),
        ]);
    }

    /**
     * DELETE /api/products/{id}
     * Soft delete a product.
     */
    public function softDelete(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        ActivityLog::log("Suppression (soft) du produit ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Product #{$id} has been soft deleted.",
        ]);
    }

    /**
     * DELETE /api/products/{id}/force
     * Permanently delete a product.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->forceDelete();

        ActivityLog::log("Suppression permanente du produit ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Product #{$id} has been permanently deleted.",
        ]);
    }

    /**
     * PATCH /api/products/{id}/restore
     * Restore a soft-deleted product.
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->restore();

        ActivityLog::log("Restauration du produit ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Product #{$id} has been restored.",
        ]);
    }
}