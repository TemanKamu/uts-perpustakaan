<?php

use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\KategoriController;
use Illuminate\Support\Facades\Route;

// Prefix /api otomatis ditambahkan oleh bootstrap/app.php.
Route::pattern('kategori', '[0-9]+');
Route::pattern('buku', '[0-9]+');

Route::apiResource('kategori', KategoriController::class)
    ->parameters(['kategori' => 'kategori']);
Route::apiResource('buku', BukuController::class)
    ->parameters(['buku' => 'buku']);
