<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use App\Models\Product;
use App\Models\Commande;
use App\Models\Fournisseur;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\StockMovement;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use Barryvdh\DomPDF\Facade\Pdf;

class RapportController extends Controller
{
    /**
     * GET /api/rapports
     */
    public function index()
    {
        $rapports = Rapport::with('user')
            ->latest()
            ->get();

        return response()->json($rapports);
    }

    /**
     * POST /api/rapports/generate
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'nom'  => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
        ]);

        $type = $validated['type'];

        $data = [];
        $view = '';

        switch ($type) {

            /**
             * =========================================================
             * STOCK REPORT
             * =========================================================
             */
            case 'Stock':

                $products = Product::with('category')->get();

                $stats = [
                    'total_products' => $products->count(),

                    'low_stock' => $products
                        ->where('quantiteStock', '<=', 'seuilAlerte')
                        ->count(),

                    'out_of_stock' => $products
                        ->where('quantiteStock', '<=', 0)
                        ->count(),
                ];

                $data = [
                    'products' => $products,
                    'stats'    => $stats,
                ];

                $view = 'pdf.rapports.stock';

                break;

            /**
             * =========================================================
             * COMMANDE REPORT
             * =========================================================
             */
            case 'Commande':

                $commandes = Commande::with([
                    'user',
                    'fournisseur',
                    'details.product'
                ])->get();

                $stats = [
                    'total_commandes' => $commandes->count(),

                    'total_ht' => $commandes->sum('total_ht'),

                    'total_tax' => $commandes->sum('total_tax'),

                    'total_ttc' => $commandes->sum('total_ttc'),
                ];

                $data = [
                    'commandes' => $commandes,
                    'stats'     => $stats,
                ];

                $view = 'pdf.rapports.commandes';

                break;

            /**
             * =========================================================
             * FOURNISSEUR REPORT
             * =========================================================
             */
            case 'Fournisseur':

                $fournisseurs = Fournisseur::with('commandes')->get();

                $data = [
                    'fournisseurs' => $fournisseurs,
                ];

                $view = 'pdf.rapports.fournisseurs';

                break;

            /**
             * =========================================================
             * USER REPORT
             * =========================================================
             */
            case 'Utilisateur':

                $users = User::with('activityLogs')->get();

                $data = [
                    'users' => $users,
                ];

                $view = 'pdf.rapports.users';

                break;

            /**
             * =========================================================
             * ACTIVITY REPORT
             * =========================================================
             */
            case 'Activite':

                $logs = ActivityLog::with('user')
                    ->latest()
                    ->get();

                $data = [
                    'logs' => $logs,
                ];

                $view = 'pdf.rapports.activities';

                break;

            /**
             * =========================================================
             * STOCK MOVEMENT REPORT
             * =========================================================
             */
            case 'Mouvement':

                $movements = StockMovement::with([
                    'product',
                    'user'
                ])->latest()->get();

                $data = [
                    'movements' => $movements,
                ];

                $view = 'pdf.rapports.movements';

                break;

            /**
             * =========================================================
             * PRODUCT REPORT
             * =========================================================
             */
            case 'Produit':

                $products = Product::with([
                    'category',
                    'stockMovements'
                ])->get();

                $data = [
                    'products' => $products,
                ];

                $view = 'pdf.rapports.products';

                break;

            /**
             * =========================================================
             * CATEGORY REPORT
             * =========================================================
             */
            case 'Categorie':

                $categories = \App\Models\Category::with('produits')
                    ->get();

                $data = [
                    'categories' => $categories,
                ];

                $view = 'pdf.rapports.categories';

                break;

            default:

                return response()->json([
                    'message' => 'Type de rapport invalide.'
                ], 422);
        }

        /**
         * =============================================================
         * GENERATE PDF
         * =============================================================
         */
        $pdf = Pdf::loadView($view, $data);

        /**
         * =============================================================
         * SAVE FILE
         * =============================================================
         */
        $filename = 'rapport_' . time() . '.pdf';

        $path = 'rapports/' . $filename;

        Storage::disk('public')->put(
            $path,
            $pdf->output()
        );

        /**
         * =============================================================
         * SAVE RAPPORT IN DATABASE
         * =============================================================
         */
        $rapport = Rapport::create([
            'nom'           => $validated['nom'],
            'dateCreation'  => now(),
            'type'          => $type,
            'file_path'     => $path,
            'user_id'       => auth()->id(),
        ]);

        /**
         * =============================================================
         * RETURN RESPONSE
         * =============================================================
         */
        return response()->json([
            'message' => 'Rapport généré avec succès.',
            'rapport' => $rapport,
        ], 201);
    }

    /**
     * DOWNLOAD RAPPORT
     * GET /api/rapports/{id}/download
     */
    public function download($id)
    {
        $rapport = Rapport::findOrFail($id);

        if (!Storage::disk('public')->exists($rapport->file_path)) {

            return response()->json([
                'message' => 'Fichier introuvable.'
            ], 404);
        }

        return Storage::disk('public')->download(
            $rapport->file_path
        );
    }

    /**
     * DELETE RAPPORT
     * DELETE /api/rapports/{id}
     */
    public function destroy($id)
    {
        $rapport = Rapport::findOrFail($id);

        if (
            $rapport->file_path &&
            Storage::disk('public')->exists($rapport->file_path)
        ) {
            Storage::disk('public')->delete(
                $rapport->file_path
            );
        }

        $rapport->delete();

        return response()->json([
            'message' => 'Rapport supprimé avec succès.'
        ]);
    }
}