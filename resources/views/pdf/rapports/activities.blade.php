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

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
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
            text-align: left;
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
                        RAPPORT D'ACTIVITÉ
                    </div>
                </td>

            </tr>
        </table>
    </div>

    <table>

        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>

            @foreach($logs as $log)

                <tr>

                    <td>
                        {{ $log->user->name ?? 'N/A' }}
                    </td>

                    <td>
                        {{ $log->action }}
                    </td>

                    <td>
                        {{ $log->dateHeure }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>