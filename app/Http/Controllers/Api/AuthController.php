<?php

namespace App\Http\Controllers\Api; // Moving to Api namespace is cleaner

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request){
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|string', // ex: administrateur, gestionnaire
        ]);


        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'],
            // Tu peux ajouter secteur/poste ici s'ils sont dans le formulaire
        ]);

        ActivityLog::log("Nouvel utilisateur enregistré: {$user->name} ({$user->role})", $user->id);


        return response()->json([
            'user' => $user,
        ], 201);
    }

    // LOGIN
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            ActivityLog::log("Connexion de l'utilisateur: {$user->name}", $user->id);

            return response()->json([
                'user' => $user,
                'token' => $token,
                'message' => 'Login successful'
            ], 200);
        }
        
        return response()->json([
            'message' => 'The provided credentials do not match our records.'
        ], 401);
    }

    // LOGOUT
    public function logout(Request $request){
    // On récupère le token directement depuis l'utilisateur authentifié
        $user = $request->user();
        
        ActivityLog::log("Déconnexion de l'utilisateur: {$user->name}", $user->id);

        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}