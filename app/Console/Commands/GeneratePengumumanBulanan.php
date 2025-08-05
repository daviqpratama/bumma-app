<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pengumuman;
use Carbon\Carbon;

class GeneratePengumumanBulanan extends Command
{
    protected $signature = 'pengumuman:generate-bulanan';
    protected $description = 'Generate pengumuman rutin komunitas setiap bulan';

    public function handle()
    {
        $tanggalRapat = Carbon::now()->format('Y-m-25');
        $tanggalMusyawarah = Carbon::now()->format('Y-m-20');

        Pengumuman::create([
            'judul' => 'Rapat Bulanan',
            'deskripsi' => 'Rapat rutin komunitas',
            'tanggal' => $tanggalRapat,
        ]);

        Pengumuman::create([
            'judul' => 'Musyawarah Masyarakat Adat',
            'deskripsi' => 'Pembahasan kegiatan adat',
            'tanggal' => $tanggalMusyawarah,
        ]);

        $this->info('Pengumuman bulanan berhasil dibuat!');
    }
}
