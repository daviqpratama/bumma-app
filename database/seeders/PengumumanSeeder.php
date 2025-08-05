<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengumuman;
use Carbon\Carbon;

class PengumumanSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        Pengumuman::create([
            'judul' => 'Pengumuman Bulan ' . $now->translatedFormat('F Y'),
            'deskripsi' => 'Ini adalah pengumuman otomatis untuk bulan ' . $now->translatedFormat('F Y'),
            'tanggal' => $now->startOfMonth()->format('Y-m-d'),
        ]);
    }
}
