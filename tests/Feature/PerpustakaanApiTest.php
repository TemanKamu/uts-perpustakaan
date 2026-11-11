<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerpustakaanApiTest extends TestCase
{
    use RefreshDatabase;

    private function bookPayload(Kategori $kategori, array $changes = []): array
    {
        return array_replace([
            'id_kategori' => $kategori->id_kategori,
            'judul_buku' => 'Pengantar SQL',
            'nama_pengarang' => 'Penulis Uji',
            'jumlah_buku' => 5,
        ], $changes);
    }

    public function test_empty_lists_are_json_arrays(): void
    {
        foreach (['kategori', 'buku'] as $resource) {
            $this->getJson('/api/'.$resource)->assertOk()->assertExactJson([
                'message' => 'Daftar '.$resource.' berhasil diambil.',
                'data' => [],
            ]);
        }
    }

    public function test_category_crud_with_custom_primary_key_and_whitespace_trimming(): void
    {
        $id = $this->postJson('/api/kategori', ['nama_kategori' => '  Teknologi  '])
            ->assertCreated()->assertJsonPath('data.nama_kategori', 'Teknologi')
            ->json('data.id_kategori');
        $this->getJson('/api/kategori')->assertOk()->assertJsonPath('data.0.id_kategori', $id);
        $this->getJson('/api/kategori/'.$id)->assertOk()->assertJsonPath('data.id_kategori', $id);
        $this->putJson('/api/kategori/'.$id, ['nama_kategori' => 'Teknologi'])
            ->assertOk(); // Nama milik dirinya sendiri tidak dianggap duplikat.
        $this->patchJson('/api/kategori/'.$id, ['nama_kategori' => 'Komputer'])
            ->assertOk()->assertJsonPath('data.nama_kategori', 'Komputer');
        $this->deleteJson('/api/kategori/'.$id)->assertOk();
        $this->getJson('/api/kategori/'.$id)->assertNotFound();
        $this->assertDatabaseCount('tb_kategori', 0);
    }

    public function test_category_validation_rejects_empty_duplicate_and_oversized_names(): void
    {
        Kategori::create(['nama_kategori' => 'Fiksi']);
        foreach (['', '   ', 'Fiksi', str_repeat('a', 101), ['bukan string']] as $name) {
            $this->postJson('/api/kategori', ['nama_kategori' => $name])
                ->assertUnprocessable()->assertJsonValidationErrors('nama_kategori');
        }
        $this->assertDatabaseCount('tb_kategori', 1);
    }

    public function test_category_update_cannot_take_another_category_name(): void
    {
        $a = Kategori::create(['nama_kategori' => 'A']);
        $b = Kategori::create(['nama_kategori' => 'B']);
        $this->putJson('/api/kategori/'.$b->id_kategori, ['nama_kategori' => 'A'])
            ->assertUnprocessable()->assertJsonValidationErrors('nama_kategori');
        $this->assertSame('B', $b->fresh()->nama_kategori);
    }

    public function test_book_crud_and_category_relationship(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Teknologi']);
        $payload = $this->bookPayload($kategori, ['tahun_terbit' => 2022, 'tgl_terima' => '2026-11-11']);
        $response = $this->postJson('/api/buku', $payload)->assertCreated()
            ->assertJsonPath('data.kategori.nama_kategori', 'Teknologi')
            ->assertJsonPath('data.tgl_terima', '2026-11-11');
        $id = $response->json('data.id_buku');
        $time = $response->json('data.tgl_input');
        $this->assertNotNull($time);
        $this->getJson('/api/buku')->assertOk()->assertJsonPath('data.0.id_buku', $id);
        $this->getJson('/api/buku/'.$id)->assertOk()->assertJsonPath('data.id_buku', $id);
        $this->getJson('/api/kategori/'.$kategori->id_kategori)
            ->assertOk()->assertJsonPath('data.buku.0.id_buku', $id);
        $this->putJson('/api/buku/'.$id, [...$payload, 'jumlah_buku' => 8])
            ->assertOk()->assertJsonPath('data.jumlah_buku', 8)->assertJsonPath('data.tgl_input', $time);
        $this->deleteJson('/api/buku/'.$id)->assertOk();
        $this->getJson('/api/buku/'.$id)->assertNotFound();
        $this->assertDatabaseCount('tb_kategori', 1);
    }

    public function test_book_defaults_and_optional_fields(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Sains']);
        $payload = $this->bookPayload($kategori);
        unset($payload['jumlah_buku']);
        $this->postJson('/api/buku', $payload)->assertCreated()
            ->assertJsonPath('data.jumlah_buku', 0)->assertJsonPath('data.penerbit', null);
    }

    public function test_invalid_book_inputs_are_422_and_do_not_create_records(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Sains']);
        $cases = [
            ['id_kategori', 999], ['id_kategori', 0], ['id_kategori', null],
            ['judul_buku', '   '], ['judul_buku', str_repeat('a', 256)],
            ['nama_pengarang', ''], ['nama_pengarang', str_repeat('a', 151)],
            ['jumlah_buku', -1], ['jumlah_buku', 1.5], ['jumlah_buku', null],
            ['jumlah_buku', 4294967296], ['tahun_terbit', 999],
            ['tahun_terbit', now()->year + 2], ['tgl_terima', '2026-02-30'],
            ['penerbit', str_repeat('a', 151)], ['cetakan', str_repeat('a', 51)],
            ['tempat_terbit', str_repeat('a', 101)], ['asal_buku', str_repeat('a', 101)],
            ['keterangan', str_repeat('a', 10001)],
        ];
        foreach ($cases as [$field, $value]) {
            $this->postJson('/api/buku', $this->bookPayload($kategori, [$field => $value]))
                ->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        $this->assertDatabaseCount('tb_buku', 0);
    }

    public function test_patch_changes_only_supplied_fields_and_invalid_update_preserves_stock(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Sains']);
        $buku = Buku::create($this->bookPayload($kategori, ['penerbit' => 'Penerbit Uji']));
        $this->patchJson('/api/buku/'.$buku->id_buku, ['jumlah_buku' => 8, 'penerbit' => null])
            ->assertOk()->assertJsonPath('data.jumlah_buku', 8)
            ->assertJsonPath('data.judul_buku', 'Pengantar SQL')->assertJsonPath('data.penerbit', null);
        $this->patchJson('/api/buku/'.$buku->id_buku, ['jumlah_buku' => -1])
            ->assertUnprocessable();
        $this->assertSame(8, $buku->fresh()->jumlah_buku);
        $this->putJson('/api/buku/'.$buku->id_buku, ['jumlah_buku' => 10])
            ->assertUnprocessable()->assertJsonValidationErrors(['id_kategori', 'judul_buku', 'nama_pengarang']);
    }

    public function test_category_in_use_is_409_and_becomes_deletable_after_book_deletion(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Teknologi']);
        $buku = Buku::create($this->bookPayload($kategori));
        $this->deleteJson('/api/kategori/'.$kategori->id_kategori)->assertStatus(409);
        $this->assertDatabaseHas('tb_buku', ['id_buku' => $buku->id_buku]);
        $this->deleteJson('/api/buku/'.$buku->id_buku)->assertOk();
        $this->deleteJson('/api/kategori/'.$kategori->id_kategori)->assertOk();
    }

    public function test_book_can_move_to_another_category(): void
    {
        $a = Kategori::create(['nama_kategori' => 'A']);
        $b = Kategori::create(['nama_kategori' => 'B']);
        $book = Buku::create($this->bookPayload($a));
        $this->patchJson('/api/buku/'.$book->id_buku, ['id_kategori' => $b->id_kategori])
            ->assertOk()->assertJsonPath('data.kategori.nama_kategori', 'B');
        $this->deleteJson('/api/kategori/'.$a->id_kategori)->assertOk();
        $this->deleteJson('/api/kategori/'.$b->id_kategori)->assertStatus(409);
    }

    public function test_system_fields_cannot_be_overwritten(): void
    {
        $kategori = Kategori::create(['nama_kategori' => 'Teknologi']);
        $this->postJson('/api/buku', $this->bookPayload($kategori, ['id_buku' => 123, 'tgl_input' => '2020-01-01']))
            ->assertUnprocessable()->assertJsonValidationErrors(['id_buku', 'tgl_input']);
        $this->postJson('/api/kategori', ['id_kategori' => 999, 'nama_kategori' => 'Baru'])
            ->assertUnprocessable()->assertJsonValidationErrors('id_kategori');
    }

    public function test_unknown_ids_and_malformed_ids_return_json_404_even_without_accept_header(): void
    {
        foreach (['kategori', 'buku'] as $resource) {
            foreach (['get', 'put', 'patch', 'delete'] as $method) {
                $this->{$method}('/api/'.$resource.'/999')
                    ->assertNotFound()->assertHeader('Content-Type', 'application/json')
                    ->assertExactJson(['message' => 'Data atau endpoint tidak ditemukan.']);
            }
            $this->get('/api/'.$resource.'/invalid')->assertNotFound()
                ->assertHeader('Content-Type', 'application/json')
                ->assertExactJson(['message' => 'Data atau endpoint tidak ditemukan.']);
        }
    }
}
