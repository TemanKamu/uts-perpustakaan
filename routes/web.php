<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'project' => 'UTS Perpustakaan',
        'framework' => 'Laravel '.app()->version(),
        'stage' => 'REST API kategori dan buku',
        'message' => 'API tersedia pada /api/kategori dan /api/buku.',
    ]);
});
