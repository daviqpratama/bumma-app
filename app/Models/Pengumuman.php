<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
     protected $table = 'pengumuman';
    use HasFactory;

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'judul', 'deskripsi', 'tanggal',
    ];
}
