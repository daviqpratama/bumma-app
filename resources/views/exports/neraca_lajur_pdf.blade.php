<!DOCTYPE html>
<html>
<head>
    <title>Export PDF Neraca Lajur</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        td, th { border: 1px solid #000; padding: 5px; font-size: 12px; text-align: right; }
        th { background-color: #eee; text-align: center; }
        td:first-child, th:first-child { text-align: left; }
    </style>
</head>
<body>
    <h3>Neraca Lajur - {{ $tanggal ?? 'Semua Tanggal' }}</h3>
    <table>
        <thead>
            <tr>
                <th>Nama Akun</th>
                <th>Saldo Awal</th>
                <th>Debet</th>
                <th>Kredit</th>
                <th>Saldo Akhir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ number_format($row['awal'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['debit'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['kredit'], 0, ',', '.') }}</td>
                    <td>{{ number_format($row['akhir'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
