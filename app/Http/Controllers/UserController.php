<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * List all users with optional filters & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'role'     => ['sometimes', Rule::in(['administrateur', 'gestionnaire'])],
            'secteur'  => ['sometimes', 'string', 'max:255'],
            'search'   => ['sometimes', 'string', 'max:255'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $users = User::query()
            ->when($request->filled('role'),    fn($q) => $q->where('role', $request->role))
            ->when($request->filled('secteur'), fn($q) => $q->where('secteur', $request->secteur))
            ->when($request->filled('search'),  fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',  'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            }))
            ->select(['id', 'name', 'email', 'role', 'secteur', 'poste', 'niveau_acces', 'profile_picture', 'email_verified_at', 'created_at'])
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $users->items(),      // 👈 table rows
            'meta' => [
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'total' => $users->total(),
            'per_page' => $users->perPage(),
            ]
        ]);
    }

    /**
     * POST /api/users
     * Create a new user.
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'         => ['sometimes', Rule::in(['administrateur', 'gestionnaire'])],
            'secteur'      => ['nullable', 'string', 'max:255'],
            'poste'        => ['nullable', 'string', 'max:255'],
            'niveau_acces' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'role'         => $validated['role']         ?? 'gestionnaire',
            'secteur'      => $validated['secteur']      ?? null,
            'poste'        => $validated['poste']        ?? null,
            'niveau_acces' => $validated['niveau_acces'] ?? null,
        ]);

        ActivityLog::log("Création de l'utilisateur: {$user->name} ({$user->email})");

        return response()->json([
            'success' => true,
            'message' => 'User created successfully.',
            'data'    => $user->only(['id', 'name', 'email', 'role', 'secteur', 'poste', 'niveau_acces', 'profile_picture', 'created_at']),
        ], 201);
    }

    /**
     * GET /api/users/{id}
     * Return a single user.
     */
    public function show(int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user->only(['id', 'name', 'email', 'role', 'secteur', 'poste', 'profile_picture', 'niveau_acces', 'created_at']),
        ]);
    }

    /**
     * PUT /api/users/{id}
     * Update an existing user.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'         => ['sometimes', 'string', 'max:255'],
            'email'        => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'     => ['sometimes', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'         => ['sometimes', Rule::in(['administrateur', 'gestionnaire'])],
            'secteur'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'poste'        => ['sometimes', 'nullable', 'string', 'max:255'],
            'niveau_acces' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log("Mise à jour de l'utilisateur: {$user->name}");

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully.',
            'data'    => $user->fresh()->only(['id', 'name', 'email', 'role', 'secteur', 'poste', 'niveau_acces', 'profile_picture', 'updated_at']),
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Soft delete a user (requires SoftDeletes trait on User model).
     */
    public function softDelete(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        ActivityLog::log("Suppression (soft) de l'utilisateur ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "User #{$id} has been soft deleted.",
        ]);
    }

    /**
     * DELETE /api/users/{id}/force
     * Permanently delete a user from the database.
     */
    public function forceDelete(int $id): JsonResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->forceDelete();

        ActivityLog::log("Suppression permanente de l'utilisateur ID #{$id}");

        return response()->json([
            'success' => true,
            'message' => "User #{$id} has been permanently deleted.",
        ]);
    }

    /**
     * PATCH /api/users/{id}/restore
     * Restore a soft-deleted user.
     */
    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        ActivityLog::log("Restauration de l'utilisateur: {$user->name}");

        return response()->json([
            'success' => true,
            'message' => "User #{$id} has been restored.",
            'data'    => $user->fresh()->only(['id', 'name', 'email', 'role', 'secteur', 'poste', 'niveau_acces', 'profile_picture']),
        ]);
    }

    public function uploadProfilePicture(Request $request, $id = null): JsonResponse{
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $user = $id ? User::findOrFail($id) : auth()->user();

        $oldPath = $user->getRawOriginal('profile_picture');
        if ($oldPath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('image')->store('profiles', 'public');
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'message'         => 'Profile picture updated',
            'profile_picture' => asset('storage/' . $path),
        ]);
    }
}