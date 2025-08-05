@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="text-3xl font-bold mb-2">Selamat Datang Admin {{ auth()->user()->name }}</h1>
            <p class="text-gray-600">Kelola data keuangan dan laporan BUMMA dengan efisien</p>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center bg-light">
                <h6>Total Pendapatan</h6>
                <strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center bg-light">
                <h6>Total Biaya Operasional</h6>
                <strong>Rp {{ number_format($totalBiaya, 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center bg-light">
                <h6>Keuntungan Bersih</h6>
                <strong>Rp {{ number_format($keuntungan, 0, ',', '.') }}</strong>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center bg-light">
                <h6>Jumlah Transaksi</h6>
                <strong>{{ $jumlahTransaksi }}</strong>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="row">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Grafik Pendapatan vs Biaya</h6>
                <canvas id="lineChart"></canvas>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h6 class="mb-2">Distribusi Pengeluaran</h6>
                <canvas id="pieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Informasi Tambahan -->
    <div class="row mt-4">
        <div class="col-md-6">
    <h5>Pengumuman Komunitas</h5>
    <ul>
        @forelse($pengumuman as $item)
            <li>📌 {{ $item->judul }} - {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</li>
        @empty
            <li>Tidak ada pengumuman komunitas.</li>
        @endforelse
    </ul>
</div>

        <div class="col-md-6">
    <h5>Notifikasi Transaksi Terbaru</h5>
    <ul>
        @foreach($notifikasiTransaksi as $notif)
            @if($notif->nominal_kredit > 0)
                <li>✅ Transfer Masuk - {{ date('d M Y H:i', strtotime($notif->tanggal)) }}</li>
            @else
                <li>❌ Transfer Keluar - {{ date('d M Y H:i', strtotime($notif->tanggal)) }}</li>
            @endif
        @endforeach
    </ul>
</div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Line Chart Pendapatan vs Biaya
    const lineCtx = document.getElementById('lineChart').getContext('2d');
    new Chart(lineCtx, {
        type: 'line',
        data: {
           labels: {!! json_encode($bulanLabels) !!},
           datasets: [
              {
                label: 'Pendapatan',
                data: {!! json_encode(array_values($pendapatanBulanan->toArray())) !!},
                borderColor: 'green',
                backgroundColor: 'rgba(0, 128, 0, 0.3)',
                fill: true
              },
              {
                label: 'Biaya',
                data: {!! json_encode(array_values($biayaBulanan->toArray())) !!},
                borderColor: 'orange',
                backgroundColor: 'rgba(255, 165, 0, 0.3)',
                fill: true
              }
           ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Pie Chart Distribusi Pengeluaran
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($distribusiPengeluaran->toArray())) !!},
            datasets: [{
                data: {!! json_encode(array_values($distribusiPengeluaran->toArray())) !!},
                backgroundColor: [
                    '#264653', '#2a9d8f', '#e9c46a',
                    '#f4a261', '#e76f51', '#a9def9',
                    '#fde2e4', '#d8e2dc'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
</script>
@endpush


 