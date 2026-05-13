<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * List all categories with optional search and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'trashed'  => ['sometimes', 'boolean'],
        ]);

        $categories = Category::query()
            ->when($request->boolean('trashed'), fn($q) => $q->onlyTrashed())
            ->when($request->filled('search'), fn($q) => $q->where('nom', 'like', '%' . $request->search . '%'))
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data'    => $categories->items(),
            'meta'    => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'total'        => $categories->total(),
                'per_page'     => $categories->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/categories
     * Create a new category.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
        ]);

        $category = Category::create($validated);

        ActivityLog::log("Création de la catégorie: {$category->nom}");

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data'    => $category,
        ], 201);
    }

    /**
     * GET /api/categories/{id}
     * Return a single category with its products count.
     */
    public function show(int $id): JsonResponse
    {
        $category = Category::withCount('produits')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $category,
        ]);
    }

    /**
     * PUT /api/categories/{id}
     * Update an existing category.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('categories', 'nom')->ignore($category->id)],
        ]);

        $category->update($validated);

        ActivityLog::log("Mise à jour de la catégorie: {$category->nom}");

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
            'data'    => $category,
        ]);
    }

    /**
     * DELETE /api/categories/{id}
     * Soft delete a category.
     */
    public function softDelete(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        ActivityLog::log("Suppression (soft) de la catégorie ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Category #{$id} has been soft deleted.",
        ]);
    }

    /**
     * DELETE /api/categories/{id}/force
     * Permanently delete a category.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->forceDelete();

        ActivityLog::log("Suppression permanente de la catégorie ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Category #{$id} has been permanently deleted.",
        ]);
    }

    /**
     * PATCH /api/categories/{id}/restore
     * Restore a soft-deleted category.
     */
    public function restore(int $id): JsonResponse
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        ActivityLog::log("Restauration de la catégorie ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "Category #{$id} has been restored.",
            'data'    => $category,
        ]);
    }
}