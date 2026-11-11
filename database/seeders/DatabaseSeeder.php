<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data contoh UTS; bukan data yang diambil dari jurnal.
        // Tidak mereset data yang sudah diedit ketika seeder diulang.
        $kategori = Kategori::firstOrCreate(['nama_kategori' => 'Teknologi']);
        Kategori::firstOrCreate(['nama_kategori' => 'Fiksi']);

        $kategori->buku()->firstOrCreate(
            [
                'judul_buku' => 'Dasar-Dasar Komputer',
                'nama_pengarang' => 'Andi Pratama',
            ],
            [
                'penerbit' => 'Pustaka Edukasi',
                'tahun_terbit' => 2022,
                'cetakan' => 'Pertama',
                'tempat_terbit' => 'Jakarta',
                'tgl_terima' => '2026-11-11',
                'asal_buku' => 'Pembelian',
                'jumlah_buku' => 5,
                'keterangan' => 'Data contoh untuk pengujian UTS.',
            ],
        );
    }
}
