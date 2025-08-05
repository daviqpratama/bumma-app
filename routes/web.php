<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SaldoAwalController;
use App\Http\Controllers\JurnalUmumController;
use App\Http\Controllers\BukuBesarController;
use App\Http\Controllers\NeracaSaldoController;
use App\Http\Controllers\JurnalPenyesuaianController;
use App\Http\Controllers\NeracaLajurController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LaporanKinerjaController;
use App\Http\Controllers\RiwayatTransaksiController;
use App\Http\Controllers\DistribusiSosialController;

Route::get('/', fn () => redirect()->route('dashboard'));

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return $user->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : ($user->role === 'user'
                ? redirect()->route('user.dashboard')
                : abort(403, 'Role tidak dikenali.'));
    })->name('dashboard');

    // Dashboard routes
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');

    // ✅ Laporan Kinerja
     Route::get('/laporan-kinerja', [LaporanKinerjaController::class, 'index'])->name('laporan.kinerja');

    // ---------------------------
    // ADMIN ROUTES ONLY
   Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboardTransaksi'])->name('admin.dashboard');



    // ---------------------------
    Route::middleware(['role:admin'])->group(function () {
        // ✅ Saldo Awal
        Route::resource('/saldo-awal', SaldoAwalController::class)->except(['show']);

        // ✅ Jurnal Umum
        Route::get('/jurnal-umum', [JurnalUmumController::class, 'index'])->name('jurnal-umum.index');
        Route::post('/jurnal-umum', [JurnalUmumController::class, 'store'])->name('jurnal-umum.store');
        Route::get('/jurnal-umum/{id}/edit', [JurnalUmumController::class, 'edit'])->name('jurnal-umum.edit');
        Route::put('/jurnal-umum/{id}', [JurnalUmumController::class, 'update'])->name('jurnal-umum.update');
        Route::delete('/jurnal-umum/{id}', [JurnalUmumController::class, 'destroy'])->name('jurnal-umum.destroy');

        // ✅ Transaksi (DITAMBAHKAN)
        Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
        Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');

        // ✅ Buku Besar
        Route::get('/buku-besar', [BukuBesarController::class, 'index'])->name('buku-besar.index');
        Route::get('/buku-besar/export/{type}', [BukuBesarController::class, 'export'])->name('buku-besar.export');

        // ✅ Neraca Saldo
        Route::get('/neraca-saldo', [NeracaSaldoController::class, 'index'])->name('neraca-saldo.index');

        // ✅ Jurnal Penyesuaian
        Route::get('/jurnal-penyesuaian', [JurnalPenyesuaianController::class, 'index'])->name('jurnal-penyesuaian.index');
        Route::post('/jurnal-penyesuaian/generate', [JurnalPenyesuaianController::class, 'generate'])->name('jurnal-penyesuaian.generate');
        Route::get('/jurnal-penyesuaian/export/pdf', [JurnalPenyesuaianController::class, 'exportPdf'])->name('jurnal-penyesuaian.export.pdf');
        Route::get('/jurnal-penyesuaian/export/excel', [JurnalPenyesuaianController::class, 'exportExcel'])->name('jurnal-penyesuaian.export.excel');

       // ✅ Neraca Lajur
Route::get('/neraca-lajur', [NeracaLajurController::class, 'index'])->name('neraca-lajur.index');
Route::get('/neraca-lajur/export', [NeracaLajurController::class, 'export'])->name('neraca-lajur.export');


        // ✅ Laporan Keuangan
        Route::get('/laporan-keuangan', [LaporanKeuanganController::class, 'index'])->name('laporan-keuangan');
        Route::get('/laporan-keuangan/sisa-hasil-usaha', [LaporanKeuanganController::class, 'sisaHasilUsaha'])->name('laporan-keuangan.sisa-hasil-usaha');
        Route::get('/laporan-keuangan/posisi-keuangan', [LaporanKeuanganController::class, 'posisiKeuangan'])->name('laporan-keuangan.posisi-keuangan');
        Route::get('/laporan-keuangan/perubahan-ekuitas', [LaporanKeuanganController::class, 'perubahanEkuitas'])->name('laporan-keuangan.perubahan-ekuitas');
        Route::get('/laporan-keuangan/arus-kas', [LaporanKeuanganController::class, 'arusKas'])->name('laporan-keuangan.arus-kas');
    });

    // ---------------------------
    // USER ROUTES ONLY
    // ---------------------------
    Route::middleware(['role:user'])->group(function () {
        Route::view('/daftar_usaha', 'daftar_usaha')->name('daftar_usaha');
        Route::get('/riwayat-transaksi', [RiwayatTransaksiController::class, 'index'])->name('riwayat_transaksi');
        Route::get('/distribusi-sosial', [DistribusiSosialController::class, 'index'])->name('distribusi_dana_sosial');
        Route::view('/kehutanan', 'kehutanan.index')->name('kehutanan.index');
        Route::view('/ekowisata', 'ekowisata.index')->name('ekowisata.index');
        Route::view('/pertanian', 'pertanian.index')->name('pertanian.index');
        Route::view('/peternakan', 'peternakan.index')->name('peternakan.index');
        Route::view('/perikanan', 'perikanan.index')->name('perikanan.index');
    });

    // ✅ Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ✅ Transaksi
Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
Route::get('/transaksi/create', [TransaksiController::class, 'create'])->name('transaksi.create');
Route::post('/transaksi', [TransaksiController::class, 'store'])->name('transaksi.store');
Route::get('/akun', function () {
    return view('akun.index'); // pastikan view-nya ada
})->name('akun.index');


Route::get('/buku-besar/export/{type}', [BukuBesarController::class, 'export'])->name('buku-besar.export');
Route::get('/buku-besar/export-pdf', [BukuBesarController::class, 'exportPdf'])->name('buku-besar.exportPdf');
Route::get('/buku-besar/export-excel', [BukuBesarController::class, 'exportExcel'])->name('buku-besar.exportExcel');


});
