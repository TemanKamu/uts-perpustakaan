<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_buku', function (Blueprint $table) {
            $table->id('id_buku');
            $table->foreignId('id_kategori')
                ->constrained('tb_kategori', 'id_kategori')
                ->restrictOnUpdate()
                ->restrictOnDelete();
            $table->string('judul_buku', 255);
            $table->string('nama_pengarang', 150);
            $table->string('penerbit', 150)->nullable();
            $table->unsignedSmallInteger('tahun_terbit')->nullable();
            $table->string('cetakan', 50)->nullable();
            $table->string('tempat_terbit', 100)->nullable();
            $table->date('tgl_terima')->nullable();
            $table->string('asal_buku', 100)->nullable();
            $table->unsignedInteger('jumlah_buku')->default(0);
            $table->text('keterangan')->nullable();
            // Diisi database sekali ketika INSERT, bukan setiap UPDATE.
            $table->timestamp('tgl_input')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_buku');
    }
};
