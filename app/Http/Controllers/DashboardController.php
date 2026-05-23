<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Commande;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Models\Category;
use App\Models\Fournisseur;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Return all dashboard data in a single JSON response.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'kpis'             => $this->kpis(),
            'stock_alerts'     => $this->stockAlerts(),
            'recent_activity'  => $this->recentActivity(),
            'orders_chart'     => $this->ordersChart(),
            'movements_chart'  => $this->movementsChart(),
            'top_products'     => $this->topProducts(),
            'category_dist'    => $this->categoryDistribution(),
        ]);
    }

    // ─────────────────────────────────────────────
    //  KPI Cards
    // ─────────────────────────────────────────────
    private function kpis(): array
    {
        $now        = Carbon::now();
        $startMonth = $now->copy()->startOfMonth();
        $endMonth   = $now->copy()->endOfMonth();

        $totalProducts     = Product::count();
        $lowStockCount     = Product::whereColumn('quantiteStock', '<=', 'seuilAlerte')->count();
        $totalFournisseurs = Fournisseur::count();

        $ordersThisMonth = Commande::whereBetween('dateCommande', [$startMonth, $endMonth])->count();

        $revenueThisMonth = Commande::whereBetween('dateCommande', [$startMonth, $endMonth])->sum('total_ttc');

        $pendingOrders = Commande::where('statut', 'en_attente')->count();

        return [
            ['label' => 'Total Produits',      'value' => $totalProducts,     'icon' => 'box',   'color' => 'blue'],
            ['label' => 'Alertes Stock',        'value' => $lowStockCount,     'icon' => 'alert', 'color' => 'red'],
            ['label' => 'Commandes ce mois',   'value' => $ordersThisMonth,   'icon' => 'cart',  'color' => 'green'],
            ['label' => 'Revenu ce mois',      'value' => number_format((float)$revenueThisMonth, 2), 'icon' => 'money', 'color' => 'purple', 'prefix' => 'MAD'],
            ['label' => 'Fournisseurs',        'value' => $totalFournisseurs, 'icon' => 'truck', 'color' => 'orange'],
            ['label' => 'Commandes en attente','value' => $pendingOrders,     'icon' => 'clock', 'color' => 'yellow'],
        ];
    }

    // ─────────────────────────────────────────────
    //  Products below or at alert threshold
    // ─────────────────────────────────────────────
    private function stockAlerts(): array
    {
        return Product::with('category')
            ->whereColumn('quantiteStock', '<=', 'seuilAlerte')
            ->orderBy('quantiteStock')
            ->limit(8)
            ->get()
            ->map(fn($p) => [
                'id'            => $p->id,
                'nom'           => $p->nom,
                'codeBarre'     => $p->codeBarre,
                'quantiteStock' => $p->quantiteStock,
                'seuilAlerte'   => $p->seuilAlerte,
                'category'      => $p->category?->nom,
            ])
            ->toArray();
    }

    // ─────────────────────────────────────────────
    //  Recent activity logs (last 10)
    // ─────────────────────────────────────────────
    private function recentActivity(): array
    {
        return ActivityLog::with('user:id,name,role')
            ->orderByDesc('dateHeure')
            ->limit(10)
            ->get()
            ->map(fn($log) => [
                'id'         => $log->id,
                'action'     => $log->action,
                'dateHeure'  => $log->dateHeure?->toIso8601String(),
                'user'       => $log->user ? [
                    'id'   => $log->user->id,
                    'name' => $log->user->name,
                    'role' => $log->user->role,
                ] : null,
            ])
            ->toArray();
    }

    // ─────────────────────────────────────────────
    //  Orders per month (last 6 months)
    // ─────────────────────────────────────────────
    private function ordersChart(): array
    {
        $rows = Commande::select(
            DB::raw('EXTRACT(YEAR  FROM "dateCommande")::int AS year'),
            DB::raw('EXTRACT(MONTH FROM "dateCommande")::int AS month'),
            DB::raw('COUNT(*)                                AS total_orders'),
            DB::raw('SUM(total_ttc)                          AS total_revenue')
        )
            ->where('dateCommande', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy(DB::raw('EXTRACT(YEAR FROM "dateCommande")'), DB::raw('EXTRACT(MONTH FROM "dateCommande")'))
            ->orderBy(DB::raw('EXTRACT(YEAR FROM "dateCommande")'))
            ->orderBy(DB::raw('EXTRACT(MONTH FROM "dateCommande")'))
            ->get();

        return $rows->map(fn($r) => [
            'label'         => Carbon::createFromDate((int)$r->year, (int)$r->month, 1)->translatedFormat('M Y'),
            'total_orders'  => (int) $r->total_orders,
            'total_revenue' => (float) $r->total_revenue,
        ])->toArray();
    }

    // ─────────────────────────────────────────────
    //  Stock movements per day (last 14 days)
    // ─────────────────────────────────────────────
    private function movementsChart(): array
    {
        $rows = StockMovement::select(
            DB::raw('"dateheure"::date AS day'),
            DB::raw("SUM(CASE WHEN type ILIKE 'entr%' THEN quantite ELSE 0 END) AS entrees"),
            DB::raw("SUM(CASE WHEN type ILIKE 'sort%' THEN quantite ELSE 0 END) AS sorties")
        )
            ->where('dateheure', '>=', Carbon::now()->subDays(13)->startOfDay())
            ->groupBy(DB::raw('"dateheure"::date'))
            ->orderBy(DB::raw('"dateheure"::date'))
            ->get();

        return $rows->map(fn($r) => [
            'day'     => $r->day,
            'entrees' => (int) $r->entrees,
            'sorties' => (int) $r->sorties,
        ])->toArray();
    }

    // ─────────────────────────────────────────────
    //  Top 5 most moved products this month
    // ─────────────────────────────────────────────
    private function topProducts(): array
    {
        return StockMovement::select('product_id', DB::raw('SUM(quantite) as total_moved'))
            ->where('dateheure', '>=', Carbon::now()->startOfMonth())
            ->where('dateheure', '<=', Carbon::now()->endOfMonth())
            ->groupBy('product_id')
            ->orderByDesc('total_moved')
            ->limit(5)
            ->with('product:id,nom,quantiteStock')
            ->get()
            ->map(fn($m) => [
                'product_id'   => $m->product_id,
                'nom'          => $m->product?->nom,
                'quantiteStock'=> $m->product?->quantiteStock,
                'total_moved'  => (int) $m->total_moved,
            ])
            ->toArray();
    }

    // ─────────────────────────────────────────────
    //  Product count per category (pie/donut)
    // ─────────────────────────────────────────────
    private function categoryDistribution(): array
    {
        return Category::withCount('produits')
            ->orderByDesc('produits_count')
            ->get()
            ->map(fn($c) => [
                'category' => $c->nom,
                'count'    => $c->produits_count,
            ])
            ->toArray();
    }
}