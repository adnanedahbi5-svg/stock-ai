<!-- ========================================================= -->
<!-- resources/views/pdf/rapports/fournisseurs.blade.php -->
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
                        RAPPORT FOURNISSEURS
                    </div>
                </td>

            </tr>
        </table>
        
    </div>

    <table>

        <thead>

            <tr>
                <th>Nom</th>
                <th>Contact</th>
                <th>Adresse</th>
                <th>Nombre Commandes</th>
            </tr>

        </thead>

        <tbody>

            @foreach($fournisseurs as $fournisseur)

                <tr>

                    <td>{{ $fournisseur->nom }}</td>

                    <td>{{ $fournisseur->contact }}</td>

                    <td>{{ $fournisseur->adresse }}</td>

                    <td>
                        {{ $fournisseur->commandes->count() }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>