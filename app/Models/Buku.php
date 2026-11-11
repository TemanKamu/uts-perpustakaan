<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Buku extends Model
{
    protected $table = 'tb_buku';

    protected $primaryKey = 'id_buku';

    public $timestamps = false;

    // PK dan tgl_input tidak diterima melalui mass assignment.
    protected $fillable = [
        'id_kategori', 'judul_buku', 'nama_pengarang', 'penerbit',
        'tahun_terbit', 'cetakan', 'tempat_terbit', 'tgl_terima',
        'asal_buku', 'jumlah_buku', 'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'id_kategori' => 'integer',
            'tahun_terbit' => 'integer',
            'jumlah_buku' => 'integer',
            'tgl_terima' => 'date:Y-m-d',
            'tgl_input' => 'datetime',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}
