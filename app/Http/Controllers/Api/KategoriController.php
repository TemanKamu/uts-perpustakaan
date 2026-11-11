<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Daftar kategori berhasil diambil.',
            'data' => Kategori::withCount('buku')->orderBy('id_kategori')->get(),
        ]);
    }

    public function store(KategoriRequest $request): JsonResponse
    {
        $kategori = Kategori::create($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => $kategori,
        ], 201);
    }

    public function show(Kategori $kategori): JsonResponse
    {
        return response()->json([
            'message' => 'Detail kategori berhasil diambil.',
            'data' => $kategori->load('buku'),
        ]);
    }

    public function update(KategoriRequest $request, Kategori $kategori): JsonResponse
    {
        $kategori->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => $kategori->refresh(),
        ]);
    }

    public function destroy(Kategori $kategori): JsonResponse
    {
        if ($kategori->buku()->exists()) {
            return $this->categoryInUse();
        }

        try {
            $kategori->delete();
        } catch (QueryException $exception) {
            // FK tetap melindungi data jika buku dibuat setelah pengecekan exists.
            if (in_array((int) ($exception->errorInfo[1] ?? 0), [1451, 787], true)) {
                return $this->categoryInUse();
            }

            throw $exception;
        }

        return response()->json(['message' => 'Kategori berhasil dihapus.'], 200);
    }

    private function categoryInUse(): JsonResponse
    {
        return response()->json([
            'message' => 'Kategori masih digunakan oleh buku dan tidak dapat dihapus.',
        ], 409);
    }
}
