<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;

class BukuController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Daftar buku berhasil diambil.',
            'data' => Buku::with('kategori')->orderBy('id_buku')->get(),
        ]);
    }

    public function store(BukuRequest $request): JsonResponse
    {
        $buku = Buku::create($request->validated());

        return response()->json([
            'message' => 'Buku berhasil ditambahkan.',
            // Baca kembali default jumlah_buku dan tgl_input dari database.
            'data' => $buku->refresh()->load('kategori'),
        ], 201);
    }

    public function show(Buku $buku): JsonResponse
    {
        return response()->json([
            'message' => 'Detail buku berhasil diambil.',
            'data' => $buku->load('kategori'),
        ]);
    }

    public function update(BukuRequest $request, Buku $buku): JsonResponse
    {
        $buku->update($request->validated());

        return response()->json([
            'message' => 'Buku berhasil diperbarui.',
            'data' => $buku->refresh()->load('kategori'),
        ]);
    }

    public function destroy(Buku $buku): JsonResponse
    {
        $buku->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.'], 200);
    }
}
