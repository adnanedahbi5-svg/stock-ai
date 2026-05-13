<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #333;
            padding: 30px;
        }

        .header {
            margin-bottom: 30px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 25px;
        }

        .info-table td {
            padding: 6px 0;
            vertical-align: top;
        }

        .label {
            font-weight: bold;
            width: 180px;
        }

        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-recue {
            background: #d4edda;
            color: #155724;
        }

        .status-annulee {
            background: #f8d7da;
            color: #721c24;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.items th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table.items td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .totals {
            margin-top: 30px;
            width: 350px;
            margin-left: auto;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        .total-final {
            font-size: 16px;
            font-weight: bold;
            background: #f5f5f5;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #888;
            font-size: 11px;
        }
    </style>
</head>

<body>

    <div class="header">

        <table width="100%">
            <tr>

                <!-- Logo -->
            <td width="120" >
                <img
                    src="{{ base_path('public/fruit-bg.jpg') }}"
                    alt="Logo"
                    width="100"
                    style="background-color: white;"
                >
            </td>

                <!-- Title -->
                <td style="text-align: center;">
                    <div class="title">
                        BON DE COMMANDE
                    </div>
                </td>

            </tr>
        </table>

    </div>

    <table class="info-table">
        <tr>
            <td class="label">Commande ID :</td>
            <td>#{{ $commande->id }}</td>
        </tr>

        <tr>
            <td class="label">Date de commande :</td>
            <td>{{ $commande->dateCommande }}</td>
        </tr>

        <tr>
            <td class="label">Fournisseur :</td>
            <td>{{ $commande->fournisseur->nom ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td class="label">Créée par :</td>
            <td>{{ $commande->user->name ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td class="label">Statut :</td>
            <td>
                <span class="status status-{{ $commande->statut }}">
                    @if($commande->statut === 'en_attente')
                        En attente
                    @elseif($commande->statut === 'recue')
                        Reçue
                    @elseif($commande->statut === 'annulee')
                        Annulée
                    @else
                        {{ $commande->statut }}
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <table class="items">

        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire HT</th>
                <th>TVA (%)</th>
                <th>Montant TVA</th>
                <th>Sous-total TTC</th>
            </tr>
        </thead>

        <tbody>

            @forelse($commande->details as $detail)

                <tr>

                    <td>
                        {{ $detail->product->nom ?? 'Produit inconnu' }}
                    </td>

                    <td>
                        {{ $detail->quantity }}
                    </td>

                    <td>
                        {{ number_format($detail->unit_price_ht, 2) }} €
                    </td>

                    <td>
                        {{ number_format($detail->tax_rate, 2) }} %
                    </td>

                    <td>
                        {{ number_format($detail->tax_amount, 2) }} €
                    </td>

                    <td>
                        <strong>
                            {{ number_format($detail->subtotal_ttc, 2) }} €
                        </strong>
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="6">
                        Aucun produit trouvé dans cette commande.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="totals">

        <table>

            <tr>
                <td><strong>Total HT</strong></td>
                <td>
                    {{ number_format($commande->total_ht, 2) }} €
                </td>
            </tr>

            <tr>
                <td><strong>Total TVA</strong></td>
                <td>
                    {{ number_format($commande->total_tax, 2) }} €
                </td>
            </tr>

            <tr class="total-final">
                <td>Total TTC</td>
                <td>
                    {{ number_format($commande->total_ttc, 2) }} €
                </td>
            </tr>

        </table>

    </div>

    <div class="footer">
        Généré automatiquement par le système de gestion de stock.
    </div>

</body>

</html>