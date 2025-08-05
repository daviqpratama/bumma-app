<!DOCTYPE html>
<html>
<head>
    <title>Jurnal Penyesuaian</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <h2>Laporan Jurnal Penyesuaian</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kode Jurnal</th>
                <th>Akun</th>
                <th>Posisi</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jurnals as $jurnal)
            <tr>
                <td>{{ $jurnal->tanggal }}</td>
                <td>{{ $jurnal->kode_jurnal }}</td>
                <td>{{ $jurnal->akun->nama }}</td>
                <td>{{ ucfirst($jurnal->posisi) }}</td>
                <td>Rp{{ number_format($jurnal->nominal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total Debit:</strong> Rp{{ number_format($totalDebit, 0, ',', '.') }}</p>
    <p><strong>Total Kredit:</strong> Rp{{ number_format($totalKredit, 0, ',', '.') }}</p>
</body>
</html>
