<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display the specified user profile.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified user profile in storage.
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:administrateur,gestionnaire',
            'secteur' => 'nullable|string|max:255',
            'poste' => 'nullable|string|max:255',
            'niveau_acces' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only([
            'name', 'email', 'role', 'secteur', 'poste', 'niveau_acces'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }

    /**
     * Remove the specified user profile from storage.
     */
    public function delete(User $user)
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted successfully'
        ], 200);
    }
}
