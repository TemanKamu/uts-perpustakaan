<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PerpustakaanDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_schema_has_only_two_domain_tables_and_no_eloquent_timestamps(): void
    {
        $this->assertTrue(Schema::hasTable('tb_kategori'));
        $this->assertTrue(Schema::hasTable('tb_buku'));
        $this->assertFalse(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('sessions'));
        $this->assertFalse(Schema::hasTable('jobs'));
        $this->assertFalse(Schema::hasTable('cache'));
        $this->assertFalse(Schema::hasColumn('tb_buku', 'created_at'));
        $this->assertFalse(Schema::hasColumn('tb_buku', 'updated_at'));
        $this->assertCount(13, Schema::getColumnListing('tb_buku'));
        $this->assertCount(2, Schema::getColumnListing('tb_kategori'));
    }

    public function test_one_category_can_have_two_books_and_inverse_relationship_works(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Teknologi']);
        $a = $kategori->buku()->create(['judul_buku' => 'SQL', 'nama_pengarang' => 'A']);
        $b = $kategori->buku()->create(['judul_buku' => 'Python', 'nama_pengarang' => 'B']);
        $this->assertCount(2, $kategori->fresh()->buku);
        $this->assertTrue($a->kategori->is($kategori));
        $this->assertTrue($b->kategori->is($kategori));
        $this->assertNotSame($a->id_buku, $b->id_buku);
    }

    public function test_duplicate_category_is_rejected_by_database(): void
    {
        Kategori::create(['nama_kategori' => 'Fiksi']);
        $this->expectException(QueryException::class);
        Kategori::create(['nama_kategori' => 'Fiksi']);
    }

    public function test_book_cannot_reference_missing_category(): void
    {
        $this->expectException(QueryException::class);
        Buku::create(['id_kategori' => 999, 'judul_buku' => 'SQL', 'nama_pengarang' => 'A']);
    }

    public function test_used_category_cannot_be_deleted(): void
    {
        $this->seed();
        $kategori = Kategori::where('nama_kategori', 'Teknologi')->firstOrFail();
        $this->expectException(QueryException::class);
        $kategori->delete();
    }

    public function test_book_can_be_deleted_without_deleting_its_category(): void
    {
        $this->seed();
        $buku = Buku::firstOrFail();
        $kategori = $buku->kategori;
        $buku->delete();
        $this->assertDatabaseMissing('tb_buku', ['id_buku' => $buku->id_buku]);
        $this->assertDatabaseHas('tb_kategori', ['id_kategori' => $kategori->id_kategori]);
        $kategori->delete();
        $this->assertDatabaseMissing('tb_kategori', ['id_kategori' => $kategori->id_kategori]);
    }

    public function test_defaults_nullable_fields_and_input_time_are_preserved(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Sains']);
        $buku = $kategori->buku()->create(['judul_buku' => 'Fisika', 'nama_pengarang' => 'C'])->refresh();
        $this->assertSame(0, $buku->jumlah_buku);
        $this->assertNull($buku->penerbit);
        $this->assertNull($buku->tgl_terima);
        $this->assertNotNull($buku->tgl_input);
        // Tanggal tetap membedakan CURRENT_TIMESTAMP vs ON UPDATE CURRENT_TIMESTAMP.
        DB::table('tb_buku')->where('id_buku', $buku->id_buku)->update(['tgl_input' => '2020-01-01 00:00:00']);
        $buku->refresh();
        $waktuAwal = $buku->tgl_input->toDateTimeString();
        $buku->update(['jumlah_buku' => 10]);
        $this->assertSame($waktuAwal, $buku->refresh()->tgl_input->toDateTimeString());
    }

    public function test_seeder_is_repeatable_without_overwriting_edited_stock(): void
    {
        $this->seed();
        Buku::firstOrFail()->update(['jumlah_buku' => 9]);
        $this->seed();
        $this->assertDatabaseCount('tb_kategori', 2);
        $this->assertDatabaseCount('tb_buku', 1);
        $this->assertSame(9, Buku::firstOrFail()->jumlah_buku);
    }

    public function test_same_title_is_allowed_and_date_is_cast_correctly(): void
    {
        $this->seed();
        $buku = Buku::firstOrFail();
        $copy = Buku::create($buku->only($buku->getFillable()))->refresh();
        $this->assertDatabaseCount('tb_buku', 2);
        $this->assertSame('2026-11-11', $copy->tgl_terima->format('Y-m-d'));
        $this->assertSame(2022, $copy->tahun_terbit);
    }

    public function test_mysql_rejects_negative_stock(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('UNSIGNED diuji pada MySQL/MariaDB; SQLite tidak menegakkannya.');
        }
        $this->seed();
        $this->expectException(QueryException::class);
        Buku::firstOrFail()->update(['jumlah_buku' => -1]);
    }
}
