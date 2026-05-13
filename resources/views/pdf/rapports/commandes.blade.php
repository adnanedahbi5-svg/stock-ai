<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <style>

        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            padding: 30px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            margin-bottom: 25px;
            font-weight: bold;
        }

        .stats {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f5f5f5;
            border: 1px solid #ddd;
            padding: 10px;
        }

        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

    </style>

</head>

<body>

    <div class="title">
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
                        RAPPORT DES COMMANDES
                    </div>
                </td>

            </tr>
        </table>
        
    </div>

    <div class="stats">

        <p>
            <strong>Total Commandes:</strong>
            {{ $stats['total_commandes'] }}
        </p>

        <p>
            <strong>Total HT:</strong>
            {{ number_format($stats['total_ht'], 2) }} €
        </p>

        <p>
            <strong>Total TVA:</strong>
            {{ number_format($stats['total_tax'], 2) }} €
        </p>

        <p>
            <strong>Total TTC:</strong>
            {{ number_format($stats['total_ttc'], 2) }} €
        </p>

    </div>

    <table>

        <thead>

            <tr>
                <th>ID</th>
                <th>Fournisseur</th>
                <th>Créée Par</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total TTC</th>
            </tr>

        </thead>

        <tbody>

            @foreach($commandes as $commande)

                <tr>

                    <td>
                        #{{ $commande->id }}
                    </td>

                    <td>
                        {{ $commande->fournisseur->nom ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $commande->user->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $commande->dateCommande }}
                    </td>

                    <td>
                        {{ $commande->statut }}
                    </td>

                    <td>
                        {{ number_format($commande->total_ttc, 2) }} €
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>

</html>