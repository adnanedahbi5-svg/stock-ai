<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandeDetailController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\DashboardController;
/*
| Les routes de l'API sont préfixées par /api automatiquement
*/

// Routes publiques (Login / Register)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('users')->middleware(['auth:sanctum', 'administrateur'])->group(function () {
 
    // GET    /api/users              → list all users (with filters & pagination)
    Route::get('/',                 [UserController::class, 'index']);

    // GET    /api/users/{id}         → get a single user
    Route::get('/{id}',             [UserController::class, 'show']);
 
    // POST   /api/users              → create a new user
    Route::post('/',                [UserController::class, 'create']);
 
    // PUT    /api/users/{id}         → update an existing user
    Route::put('/{id}',             [UserController::class, 'update']);
 
    // DELETE /api/users/{id}         → soft delete a user
    Route::delete('/{id}',          [UserController::class, 'softDelete']);
 
    // DELETE /api/users/{id}/force   → permanently delete a user
    Route::delete('/{id}/force',    [UserController::class, 'forceDelete']);
 
    // PATCH  /api/users/{id}/restore → restore a soft-deleted user
    Route::patch('/{id}/restore',   [UserController::class, 'restore']);
 
});

Route::prefix('products')->middleware('auth:sanctum')->group(function () {
 
    // GET    /api/products             → list all products (with filters & pagination)
    Route::get('/',                 [ProductController::class, 'index']);

    // GET    /api/products/{id}        → get a single product
    Route::get('/{id}',             [ProductController::class, 'show']);
 
    // POST   /api/products             → create a new product
    Route::post('/',                [ProductController::class, 'create']);
 
    // PUT    /api/products/{id}        → update an existing product
    Route::put('/{id}',             [ProductController::class, 'update']);
 
    // DELETE /api/products/{id}        → soft delete a product
    Route::delete('/{id}',          [ProductController::class, 'softDelete']);
 
    // DELETE /api/products/{id}/force  → permanently delete a product
    Route::delete('/{id}/force',    [ProductController::class, 'forceDelete']);
 
    // PATCH  /api/products/{id}/restore → restore a soft-deleted product
    Route::patch('/{id}/restore',   [ProductController::class, 'restore']);
 
});

Route::prefix('fournisseurs')->middleware('auth:sanctum')->group(function () {
    Route::get('/',                [FournisseurController::class, 'index']);
    Route::get('/{id}',            [FournisseurController::class, 'show']);
    Route::post('/',               [FournisseurController::class, 'create']);
    Route::put('/{id}',            [FournisseurController::class, 'update']);
    Route::delete('/{id}',         [FournisseurController::class, 'softDelete']);
    Route::delete('/{id}/force',   [FournisseurController::class, 'forceDelete']);
    Route::patch('/{id}/restore',  [FournisseurController::class, 'restore']);
});

Route::prefix('categories')->middleware('auth:sanctum')->group(function () {
    Route::get('/',                [CategoryController::class, 'index']);
    Route::get('/{id}',            [CategoryController::class, 'show']);
    Route::post('/',               [CategoryController::class, 'create']);
    Route::put('/{id}',            [CategoryController::class, 'update']);
    Route::delete('/{id}',         [CategoryController::class, 'softDelete']);
    Route::delete('/{id}/force',   [CategoryController::class, 'forceDelete']);
    Route::patch('/{id}/restore',  [CategoryController::class, 'restore']);
});

Route::prefix('movements')->middleware('auth:sanctum')->group(function () {
    Route::get('/',                [StockMovementController::class, 'index']);
    Route::post('/',               [StockMovementController::class, 'store']);
});

Route::prefix('commandes')->middleware('auth:sanctum')->group(function () {
    Route::get('/',                [CommandeController::class, 'index']);
    Route::get('/{id}',            [CommandeController::class, 'show']);
    Route::post('/',               [CommandeController::class, 'store']);
    Route::put('/{id}',            [CommandeController::class, 'update']);
    Route::delete('/{id}',         [CommandeController::class, 'destroy']);
    Route::get('/{id}/pdf',        [CommandeController::class, 'downloadPdf']);
});

Route::prefix('commande-details')->middleware('auth:sanctum')->group(function () {
    Route::get('/',                [CommandeDetailController::class, 'index']);
    Route::get('/{id}',            [CommandeDetailController::class, 'show']);
    Route::post('/',               [CommandeDetailController::class, 'store']);
    Route::put('/{id}',            [CommandeDetailController::class, 'update']);
    Route::delete('/{id}',         [CommandeDetailController::class, 'destroy']);
});

Route::prefix('rapports')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [RapportController::class, 'index']);
    Route::post('/generate', [RapportController::class, 'generate']);
    Route::get('/{id}/download', [RapportController::class, 'download']);
    Route::delete('/{id}', [RapportController::class, 'destroy']);
});

// Routes protégées (nécessitent un Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Route pour récupérer l'utilisateur connecté via Pinia
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Activity Logs - Restricted to Admins
    Route::middleware('administrateur')->get('/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});