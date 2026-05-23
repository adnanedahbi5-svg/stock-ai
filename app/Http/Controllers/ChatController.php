<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Fournisseur;
use App\Models\Commande;
use App\Models\StockMovement;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * POST /api/chat/message
     *
     * Stateless AI chat endpoint.
     * - Accepts the user's message and the conversation history from the frontend.
     * - Injects a concise snapshot of ALL warehouse data as system context.
     * - Proxies the full conversation to the FastAPI agent (Groq / LLaMA 3.3).
     * - Returns the AI reply text.
     */
    public function simpleChat(Request $request): JsonResponse
    {
        $request->validate([
            'message'           => 'required|string|max:4000',
            'history'           => 'nullable|array',
            'history.*.role'    => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string',
        ]);

        try {

            // ── 1. Products (limit 100 to control token size) ─────────────────
            $products = Product::with('category:id,nom')
                ->limit(100)
                ->get(['id', 'nom', 'codeBarre', 'quantiteStock', 'seuilAlerte', 'category_id'])
                ->map(fn($p) => [
                    'id'        => $p->id,
                    'name'      => $p->nom,
                    'barcode'   => $p->codeBarre,
                    'stock'     => $p->quantiteStock,
                    'alert_at'  => $p->seuilAlerte,
                    'low_stock' => $p->quantiteStock <= $p->seuilAlerte,
                    'category'  => $p->category->nom ?? 'N/A',
                ])
                ->toArray();

            // ── 2. Categories ─────────────────────────────────────────────────
            $categories = Category::all(['id', 'nom'])
                ->map(fn($c) => ['id' => $c->id, 'name' => $c->nom])
                ->toArray();

            // ── 3. Fournisseurs (Suppliers) ───────────────────────────────────
            $fournisseurs = Fournisseur::all(['id', 'nom', 'contact', 'adresse'])
                ->map(fn($f) => [
                    'id'      => $f->id,
                    'name'    => $f->nom,
                    'contact' => $f->contact,
                    'address' => $f->adresse,
                ])
                ->toArray();

            // ── 4. Commandes (Orders) — last 20 only ──────────────────────────
            $commandes = Commande::with(['fournisseur:id,nom', 'details.product:id,nom'])
                ->orderBy('dateCommande', 'desc')
                ->limit(20)
                ->get(['id', 'dateCommande', 'statut', 'fournisseur_id', 'total_ht', 'total_ttc'])
                ->map(fn($c) => [
                    'id'        => $c->id,
                    'date'      => $c->dateCommande,
                    'status'    => $c->statut,
                    'supplier'  => $c->fournisseur->nom ?? 'N/A',
                    'total_ttc' => $c->total_ttc,
                    'items'     => $c->details->map(fn($d) => [
                        'product' => $d->product->nom ?? 'N/A',
                        'qty'     => $d->quantity,
                    ])->toArray(),
                ])
                ->toArray();

            // ── 5. Stock Movements — last 30 ──────────────────────────────────
            $movements = StockMovement::with('product:id,nom')
                ->orderBy('dateheure', 'desc')
                ->limit(30)
                ->get(['id', 'type', 'quantite', 'dateheure', 'localisation', 'product_id'])
                ->map(fn($m) => [
                    'type'     => $m->type,
                    'quantity' => $m->quantite,
                    'date'     => $m->dateheure,
                    'location' => $m->localisation,
                    'product'  => $m->product->nom ?? 'N/A',
                ])
                ->toArray();

            // ── 6. Activity Logs — last 30 ────────────────────────────────────
            $activities = ActivityLog::with('user:id,name')
                ->orderBy('dateHeure', 'desc')
                ->limit(30)
                ->get(['id', 'action', 'dateHeure', 'user_id'])
                ->map(fn($a) => [
                    'action' => $a->action,
                    'date'   => $a->dateHeure,
                    'user'   => $a->user->name ?? 'System/Unknown',
                ])
                ->toArray();

            // ── 7. Users ──────────────────────────────────────────────────────
            $users = User::all(['id', 'name', 'email', 'role', 'secteur', 'poste'])
                ->map(fn($u) => [
                    'id'      => $u->id,
                    'name'    => $u->name,
                    'email'   => $u->email,
                    'role'    => $u->role,
                    'secteur' => $u->secteur,
                    'poste'   => $u->poste,
                ])
                ->toArray();

            // ── Build concise system prompt ───────────────────────────────────
            $systemContent = "You are a Stock AI Assistant for a warehouse management system.\n"
                . "Answer concisely in the same language the user writes in (French or English).\n"
                . "Use the following real-time data to answer:\n\n"
                . "PRODUCTS (" . count($products) . "):\n" . json_encode($products) . "\n\n"
                . "CATEGORIES (" . count($categories) . "):\n" . json_encode($categories) . "\n\n"
                . "SUPPLIERS (" . count($fournisseurs) . "):\n" . json_encode($fournisseurs) . "\n\n"
                . "RECENT ORDERS (last 20):\n" . json_encode($commandes) . "\n\n"
                . "RECENT STOCK MOVEMENTS (last 30):\n" . json_encode($movements) . "\n\n"
                . "RECENT ACTIVITY LOGS (last 30):\n" . json_encode($activities) . "\n\n"
                . "SYSTEM USERS (" . count($users) . "):\n" . json_encode($users);

            $systemMessage = ['role' => 'system', 'content' => $systemContent];

            // ── Rebuild conversation: [system] + history + new user message ───
            $history   = $request->input('history', []);
            $history[] = ['role' => 'user', 'content' => $request->input('message')];
            $payload   = array_merge([$systemMessage], $history);

            // ── Forward to FastAPI ────────────────────────────────────────────
            $fastapiUrl = config('services.fastapi.url', 'http://127.0.0.1:8001');

            Log::info('[ChatController] Sending to FastAPI', [
                'url'             => $fastapiUrl . '/chat',
                'message_count'   => count($payload),
                'system_chars'    => strlen($systemContent),
            ]);

            $response = Http::asJson()->timeout(60)->post($fastapiUrl . '/chat', [
                'messages' => $payload,
            ]);

            // Log the full FastAPI response for debugging
            Log::info('[ChatController] FastAPI response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if ($response->failed()) {
                // Return the actual FastAPI error so you can see what went wrong
                return response()->json([
                    'reply'  => 'AI service error (HTTP ' . $response->status() . '): ' . $response->body(),
                    'error'  => true,
                ], 502);
            }

            $reply = $response->json('reply') ?? 'No response received from the AI.';

            return response()->json(['reply' => $reply]);

        } catch (\Exception $e) {

            Log::error('[ChatController] Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'reply'  => 'Error: ' . $e->getMessage(),
                'error'  => true,
            ], 500);
        }
    }
}