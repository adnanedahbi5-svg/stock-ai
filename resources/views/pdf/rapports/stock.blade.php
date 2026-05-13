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

        .stats p {
            margin: 5px 0;
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
                        RAPPORT DE STOCK
                    </div>
                </td>

            </tr>
        </table>
        
    </div>

    <div class="stats">

        <p>
            <strong>Total Produits:</strong>
            {{ $stats['total_products'] }}
        </p>

        <p>
            <strong>Stock Faible:</strong>
            {{ $stats['low_stock'] }}
        </p>

        <p>
            <strong>Rupture:</strong>
            {{ $stats['out_of_stock'] }}
        </p>

    </div>

    <table>

        <thead>

            <tr>
                <th>Produit</th>
                <th>Catégorie</th>
                <th>Stock</th>
                <th>Seuil</th>
                <th>Status</th>
            </tr>

        </thead>

        <tbody>

            @foreach($products as $product)

                <tr>

                    <td>
                        {{ $product->nom }}
                    </td>

                    <td>
                        {{ $product->category->nom ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $product->quantiteStock }}
                    </td>

                    <td>
                        {{ $product->seuilAlerte }}
                    </td>

                    <td>

                        @if($product->quantiteStock <= 0)

                            Rupture

                        @elseif(
                            $product->quantiteStock <=
                            $product->seuilAlerte
                        )

                            Faible

                        @else

                            Normal

                        @endif

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>

</html>