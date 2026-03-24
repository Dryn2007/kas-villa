<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $fileNameStr }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0 0 5px 0;
            padding: 0;
            font-size: 18px;
        }

        .header p {
            margin: 0;
            font-size: 12px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>{{ $fileNameStr }}</h2>
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="25%">Nama KK</th>
                <th width="25%" class="text-center">Lunas (Periode)</th>
                <th width="25%" class="text-center">Belum Bayar (Periode)</th>
                <th width="20%">Total Dibayar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td class="text-center">{{ $row['lunas_range'] }}</td>
                    <td class="text-center">{{ $row['belum_range'] }}</td>
                    <td>Rp {{ number_format($row['nominal_lunas'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total Keseluruhan Kas Terkumpul:</th>
                <th>Rp {{ number_format($totalDana, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

</body>

</html>
