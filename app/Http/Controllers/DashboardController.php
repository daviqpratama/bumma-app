<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pengumuman; // ✅ Tambahan

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $totalPendapatan = 350000000;
        $totalBiaya = 120000000;
        $keuntungan = $totalPendapatan - $totalBiaya;
        $jumlahTransaksi = 150;

        // ✅ Ambil pengumuman otomatis dari database
        $pengumuman = Pengumuman::orderBy('tanggal', 'desc')->get();

        if ($user->role === 'admin') {
            return view('dashboard.admin', compact(
                'totalPendapatan',
                'totalBiaya',
                'keuntungan',
                'jumlahTransaksi',
                'pengumuman' // ✅ tambahan di compact
            ));
        } elseif ($user->role === 'user') {
            return view('dashboard.user', compact('pengumuman')); // ✅ juga ke user kalau butuh
        } else {
            return view('errors.role-not-found');
        }
    }

    public function adminDashboard()
    {
        return $this->index();
    }

    public function userDashboard()
    {
        return $this->index();
    }

    /**
     * ✅ Dashboard Admin Real Data dari Transaksis
     */
    public function adminDashboardTransaksi()
    {
        $totalPendapatan = DB::table('transaksis')
            ->sum(DB::raw("REPLACE(REPLACE(nominal_kredit, 'Rp', ''), '.', '') + 0"));

        $totalBiaya = DB::table('transaksis')
            ->sum(DB::raw("REPLACE(REPLACE(nominal_debit, 'Rp', ''), '.', '') + 0"));

        $keuntungan = $totalPendapatan - $totalBiaya;
        $jumlahTransaksi = DB::table('transaksis')->count();

        $pendapatanBulanan = DB::table('transaksis')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(REPLACE(REPLACE(nominal_kredit, "Rp", ""), ".", "") + 0) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $biayaBulanan = DB::table('transaksis')
            ->selectRaw('MONTH(tanggal) as bulan, SUM(REPLACE(REPLACE(nominal_debit, "Rp", ""), ".", "") + 0) as total')
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $bulanLabels = $pendapatanBulanan->keys()->map(function($bulan) {
            return date('M', mktime(0,0,0,$bulan,1));
        });

        $distribusiPengeluaran = DB::table('transaksis')
            ->select('akun_debit', DB::raw('SUM(REPLACE(REPLACE(nominal_debit, "Rp", ""), ".", "") + 0) as total'))
            ->groupBy('akun_debit')->pluck('total', 'akun_debit');

        // ✅ Tambahan: ambil pengumuman otomatis
        $pengumuman = Pengumuman::orderBy('tanggal', 'desc')->get();

        // ✅ Notifikasi transaksi tetap
        $notifikasiTransaksi = DB::table('transaksis')
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalPendapatan',
            'totalBiaya',
            'keuntungan',
            'jumlahTransaksi',
            'pendapatanBulanan',
            'biayaBulanan',
            'distribusiPengeluaran',
            'bulanLabels',
            'notifikasiTransaksi',
            'pengumuman' // ✅ tambahan
        ));
    }
}
