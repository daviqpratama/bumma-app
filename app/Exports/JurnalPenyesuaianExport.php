<?php

namespace App\Exports;

use App\Models\JurnalUmum;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JurnalPenyesuaianExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return JurnalUmum::with('akun')
            ->where('ref', 'Penyesuaian')
            ->get()
            ->map(function($item) {
                return [
                    'Tanggal' => $item->tanggal,
                    'Kode Jurnal' => $item->kode_jurnal,
                    'Akun' => $item->akun->nama,
                    'Keterangan' => $item->keterangan,
                    'Posisi' => $item->posisi,
                    'Nominal' => $item->nominal,
                ];
            });
    }

    public function headings(): array
    {
        return ['Tanggal', 'Kode Jurnal', 'Akun', 'Keterangan', 'Posisi', 'Nominal'];
    }
}
