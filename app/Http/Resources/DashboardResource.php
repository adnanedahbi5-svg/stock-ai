<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * The resource wraps the raw associative array built in DashboardController.
     * It adds metadata (timestamp, version) and ensures a stable JSON contract
     * between the Laravel API and the Vue.js frontend.
     *
     * Frontend can always rely on every key being present (nullable, not missing).
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [

            // ── Meta ──────────────────────────────────────────────────────────
            'meta' => [
                'generatedAt' => now()->toIso8601String(),
                'timezone'    => config('app.timezone'),
                'user'        => [
                    'id'   => $request->user()?->id,
                    'name' => $request->user()?->name,
                    'role' => $request->user()?->role,
                ],
            ],

            // ── KPI Cards ─────────────────────────────────────────────────────
            // Each value displayed in the top summary cards on the dashboard.
            'kpis' => [
                'totalProducts'     => $data['kpis']['totalProducts']     ?? 0,
                'lowStockCount'     => $data['kpis']['lowStockCount']     ?? 0,
                'totalStockValue'   => $data['kpis']['totalStockValue']   ?? 0,
                'movementsToday'    => $data['kpis']['movementsToday']    ?? 0,
                'pendingOrders'     => $data['kpis']['pendingOrders']     ?? 0,
                'totalFournisseurs' => $data['kpis']['totalFournisseurs'] ?? 0,
            ],

            // ── Alerts ────────────────────────────────────────────────────────
            // Products at or below their seuilAlerte. Drives the AlertsPanel.
            // Each item: { id, nom, codeBarre, quantiteStock, seuilAlerte, categorie, severity }
            'alerts' => $data['alerts'] ?? [],

            // ── Recent Movements ─────────────────────────────────────────────
            // Last 8 stock movements. Drives the RecentMovements widget.
            // Each item: { id, type, quantite, dateheure, localisation, produit, user }
            'recentMovements' => $data['recentMovements'] ?? [],

            // ── Movements Chart ───────────────────────────────────────────────
            // Last 7 days of entree/sortie quantities. Drives the StockChart.
            // Each item: { date: 'YYYY-MM-DD', entree: int, sortie: int }
            'movementsChart' => $data['movementsChart'] ?? [],

            // ── Top Products ──────────────────────────────────────────────────
            // Top 5 products by quantiteStock. Drives TopProductsChart.
            // Each item: { nom, quantiteStock, categorie }
            'topProducts' => $data['topProducts'] ?? [],

            // ── Orders by Status ──────────────────────────────────────────────
            // Counts grouped by commande statut. Drives OrdersStatus widget.
            // Shape: { en_attente: int, recue: int, annulee: int }
            'ordersByStatus' => $data['ordersByStatus'] ?? [
                'en_attente' => 0,
                'recue'      => 0,
                'annulee'    => 0,
            ],

            // ── Activity Feed ─────────────────────────────────────────────────
            // Last 10 entries from journal_activites. Drives ActivityFeed widget.
            // Each item: { id, action, dateHeure, user, role }
            'activityFeed' => $data['activityFeed'] ?? [],

            // ── Users Overview (admin only, null for gestionnaire) ─────────────
            // Shape: { total, administrateurs, gestionnaires } | null
            'usersOverview' => $data['usersOverview'] ?? null,
        ];
    }
}