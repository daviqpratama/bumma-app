<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;
use App\Models\Transaksi;
use App\Models\JurnalUmum;
use Barryvdh\DomPDF\Facade\Pdf;

class NeracaLajurController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal;
        $namaAkun = $request->nama_akun;

        $akuns = Akun::when($namaAkun, function ($query, $namaAkun) {
            return $query->where('nama', 'like', '%' . $namaAkun . '%');
        })->get();

        $data = [];
        $totalAwal = $totalDebit = $totalKredit = $totalAkhir = 0;

        foreach ($akuns as $akun) {
            $saldoAwal = $akun->saldoAwals()->sum('debit') - $akun->saldoAwals()->sum('kredit');

            $debitTransaksi = Transaksi::where('akun_debit', $akun->id)
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal))
                ->sum('nominal_debit');

            $kreditTransaksi = Transaksi::where('akun_kredit', $akun->id)
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal))
                ->sum('nominal_kredit');

            $jurnal = JurnalUmum::where('akun_id', $akun->id)->where('ref', 'Penyesuaian')
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal));

            $debitPenyesuaian = (clone $jurnal)->where('posisi', 'debit')->sum('nominal');
            $kreditPenyesuaian = (clone $jurnal)->where('posisi', 'kredit')->sum('nominal');

            $debit = $debitTransaksi + $debitPenyesuaian;
            $kredit = $kreditTransaksi + $kreditPenyesuaian;
            $saldoAkhir = $saldoAwal + $debit - $kredit;

            $status = $saldoAkhir > 0 ? 'Debit' : ($saldoAkhir < 0 ? 'Kredit' : 'Seimbang');

            if ($saldoAwal != 0 || $debit != 0 || $kredit != 0) {
                $data[] = [
                    'nama' => $akun->nama,
                    'awal' => $saldoAwal,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'akhir' => $saldoAkhir,
                    'status' => $status,
                ];

                $totalAwal += $saldoAwal;
                $totalDebit += $debit;
                $totalKredit += $kredit;
                $totalAkhir += $saldoAkhir;
            }
        }

        return view('neraca-lajur.index', compact('data', 'tanggal', 'namaAkun', 'totalAwal', 'totalDebit', 'totalKredit', 'totalAkhir'));
    }

    public function export(Request $request)
    {
        $format = $request->format;
        $tanggal = $request->tanggal;
        $namaAkun = $request->nama_akun;

        $akuns = Akun::when($namaAkun, function ($query, $namaAkun) {
            return $query->where('nama', 'like', '%' . $namaAkun . '%');
        })->get();

        $data = [];
        $totalAwal = $totalDebit = $totalKredit = $totalAkhir = 0;

        foreach ($akuns as $akun) {
            $saldoAwal = $akun->saldoAwals()->sum('debit') - $akun->saldoAwals()->sum('kredit');

            $debitTransaksi = Transaksi::where('akun_debit', $akun->id)
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal))
                ->sum('nominal_debit');

            $kreditTransaksi = Transaksi::where('akun_kredit', $akun->id)
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal))
                ->sum('nominal_kredit');

            $jurnal = JurnalUmum::where('akun_id', $akun->id)->where('ref', 'Penyesuaian')
                ->when($tanggal, fn($q) => $q->whereDate('tanggal', '<=', $tanggal));

            $debitPenyesuaian = (clone $jurnal)->where('posisi', 'debit')->sum('nominal');
            $kreditPenyesuaian = (clone $jurnal)->where('posisi', 'kredit')->sum('nominal');

            $debit = $debitTransaksi + $debitPenyesuaian;
            $kredit = $kreditTransaksi + $kreditPenyesuaian;
            $saldoAkhir = $saldoAwal + $debit - $kredit;

            $status = $saldoAkhir > 0 ? 'Debit' : ($saldoAkhir < 0 ? 'Kredit' : 'Seimbang');

            if ($saldoAwal != 0 || $debit != 0 || $kredit != 0) {
                $data[] = [
                    'nama' => $akun->nama,
                    'awal' => $saldoAwal,
                    'debit' => $debit,
                    'kredit' => $kredit,
                    'akhir' => $saldoAkhir,
                    'status' => $status,
                ];

                $totalAwal += $saldoAwal;
                $totalDebit += $debit;
                $totalKredit += $kredit;
                $totalAkhir += $saldoAkhir;
            }
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('neraca-lajur.export-pdf', compact('data', 'tanggal', 'totalAwal', 'totalDebit', 'totalKredit', 'totalAkhir'));
            return $pdf->download('neraca_lajur.pdf');
        }

        if ($format === 'csv') {
            $filename = 'neraca_lajur.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Nama Akun', 'Saldo Awal', 'Debit', 'Kredit', 'Saldo Akhir', 'Status']);

                foreach ($data as $row) {
                    fputcsv($handle, [
                        $row['nama'],
                        $row['awal'],
                        $row['debit'],
                        $row['kredit'],
                        $row['akhir'],
                        $row['status'],
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Format tidak valid!');
    }
}
