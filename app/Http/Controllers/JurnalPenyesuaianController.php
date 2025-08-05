<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JurnalUmum;
use App\Models\SaldoAwal;
use App\Models\Akun;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
// use App\Exports\JurnalPenyesuaianExport;
// use Maatwebsite\Excel\Facades\Excel;

class JurnalPenyesuaianController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->bulan ?? now()->format('m');
        $tahun = $request->tahun ?? now()->format('Y');
        $nomorJurnal = $request->nomor_jurnal;

        $jurnals = JurnalUmum::with('akun')
            ->where('ref', 'Penyesuaian')
            ->when($bulan, fn($q) => $q->whereMonth('tanggal', $bulan))
            ->when($tahun, fn($q) => $q->whereYear('tanggal', $tahun))
            ->when($nomorJurnal, fn($q) => $q->where('kode_jurnal', 'like', "%$nomorJurnal%"))
            ->orderBy('tanggal')
            ->orderBy('kode_jurnal')
            ->get();

        $totalDebit = $jurnals->where('posisi', 'debit')->sum('nominal');
        $totalKredit = $jurnals->where('posisi', 'kredit')->sum('nominal');

        return view('jurnal-penyesuaian.index', [
            'jurnals' => $jurnals,
            'totalDebit' => $totalDebit,
            'totalKredit' => $totalKredit,
            'bulan' => $bulan,
            'tahun' => $tahun,
        ]);
    }

    public function exportPdf()
    {
        $jurnals = JurnalUmum::with('akun')
            ->where('ref', 'Penyesuaian')
            ->orderBy('tanggal')
            ->orderBy('kode_jurnal')
            ->get();

        $totalDebit = $jurnals->where('posisi', 'debit')->sum('nominal');
        $totalKredit = $jurnals->where('posisi', 'kredit')->sum('nominal');

        $pdf = Pdf::loadView('jurnal-penyesuaian.pdf', compact('jurnals', 'totalDebit', 'totalKredit'));
        return $pdf->download('jurnal-penyesuaian.pdf');
    }

    public function exportExcel()
    {
        $jurnals = JurnalUmum::with('akun')
            ->where('ref', 'Penyesuaian')
            ->orderBy('tanggal')
            ->orderBy('kode_jurnal')
            ->get();

        $filename = "jurnal-penyesuaian-" . now()->format('Ymd_His') . ".csv";

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($jurnals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Kode Jurnal', 'Akun', 'Keterangan', 'Posisi', 'Nominal']);

            foreach ($jurnals as $jurnal) {
                fputcsv($handle, [
                    \Carbon\Carbon::parse($jurnal->tanggal)->format('d-m-Y'),
                    $jurnal->kode_jurnal,
                    $jurnal->akun->nama ?? '-',
                    $jurnal->keterangan,
                    ucfirst($jurnal->posisi),
                    $jurnal->nominal,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function generate()
    {
        $tanggal = now()->toDateString();
        $kodeJurnal = 'JP-' . now()->format('my') . '-' . strtoupper(Str::random(3));

        $penyesuaian = [
            ['aset' => 'Perlengkapan', 'beban' => 'Beban Perlengkapan', 'persen' => 0.2],
            ['aset' => 'Sewa Dibayar di Muka', 'beban' => 'Beban Sewa', 'persen' => 0.25],
            ['aset' => 'Asuransi Dibayar di Muka', 'beban' => 'Beban Asuransi', 'persen' => 0.2],
            ['aset' => 'Peralatan', 'beban' => 'Beban Penyusutan', 'persen' => 0.1],
        ];

        foreach ($penyesuaian as $item) {
            $akunAset = Akun::where('nama', $item['aset'])->first();
            $akunBeban = Akun::where('nama', $item['beban'])->first();

            if ($akunAset && $akunBeban) {
                $saldo = SaldoAwal::where('akuns_id', $akunAset->id)->get();
                $total = $saldo->sum('debit') - $saldo->sum('kredit');
                $nilaiPenyesuaian = $total * $item['persen'];

                if ($nilaiPenyesuaian > 0) {
                    JurnalUmum::create([
                        'tanggal' => $tanggal,
                        'kode_jurnal' => $kodeJurnal,
                        'akun_id' => $akunBeban->id,
                        'keterangan' => 'Penyesuaian ' . strtolower($item['aset']),
                        'posisi' => 'debit',
                        'nominal' => $nilaiPenyesuaian,
                        'ref' => 'Penyesuaian',
                    ]);

                    JurnalUmum::create([
                        'tanggal' => $tanggal,
                        'kode_jurnal' => $kodeJurnal,
                        'akun_id' => $akunAset->id,
                        'keterangan' => 'Penyesuaian ' . strtolower($item['aset']),
                        'posisi' => 'kredit',
                        'nominal' => $nilaiPenyesuaian,
                        'ref' => 'Penyesuaian',
                    ]);
                }
            }
        }

        return redirect()->route('jurnal-penyesuaian.index')->with('success', 'Jurnal penyesuaian berhasil dibuat otomatis.');
    }
}
