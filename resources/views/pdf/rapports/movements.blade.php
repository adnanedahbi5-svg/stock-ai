<!-- ========================================================= -->
<!-- resources/views/pdf/rapports/movements.blade.php -->
<!-- ========================================================= -->

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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background: #f5f5f5;
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
                        RAPPORT DES MOUVEMENTS
                    </div>
                </td>

            </tr>
        </table>
        
    </div>

    <table>

        <thead>
            <tr>
                <th>Produit</th>
                <th>Type</th>
                <th>Quantité</th>
                <th>Utilisateur</th>
                <th>Localisation</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

            @foreach($movements as $movement)

                <tr>

                    <td>
                        {{ $movement->product->nom ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $movement->type }}
                    </td>

                    <td>
                        {{ $movement->quantite }}
                    </td>

                    <td>
                        {{ $movement->user->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $movement->localisation }}
                    </td>

                    <td>
                        {{ $movement->dateheure }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>