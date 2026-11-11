# Memasang Tahap 3 pada proyek yang sudah berjalan

Paket ini adalah source lengkap Laravel 13.42.0 beserta perubahan Tahap 3. Jika lo sudah mengatur `.env`, database, dan vendor pada proyek lama, cukup salin file berikut dari folder `uts-perpustakaan` dalam ZIP ke lokasi yang sama di proyek lo:

| Tindakan | File |
| --- | --- |
| Tambah | `app/Http/Controllers/Api/KategoriController.php` |
| Tambah | `app/Http/Controllers/Api/BukuController.php` |
| Tambah | `app/Http/Requests/KategoriRequest.php` |
| Tambah | `app/Http/Requests/BukuRequest.php` |
| Tambah | `routes/api.php` |
| Perbarui | `bootstrap/app.php` |
| Perbarui | `routes/web.php` |
| Tambah | `tests/Feature/PerpustakaanApiTest.php` |
| Perbarui | `tests/Feature/ApplicationTest.php` |

Jika bootstrap/app.php sudah lo modifikasi sendiri, gabungkan baris `api: __DIR__.'/../routes/api.php'` pada `withRouting` dan handler 404 pada `withExceptions`; jangan menimpa konfigurasi tambahan lo.

Migration dan model tetap memakai desain Tahap 2. Tidak perlu membuat ulang database, menjalankan `migrate:fresh`, memasang Sanctum, atau menjalankan `install:api`. Simpan `.env` dan database lo yang sudah ada.

Di terminal proyek:

```bat
composer dump-autoload
php artisan optimize:clear
php artisan route:list --path=api
php artisan serve
```

Target: 10 baris route resource. PUT dan PATCH muncul pada satu baris update masing-masing resource.

Import `postman/UTS_Perpustakaan_Laravel13_Tahap3.postman_collection.json`, kemudian Run collection berurutan dengan 1 iteration. Collection ini sudah mengaktifkan CRUD. Jika memakai file Postman lama, ubah collection variable `enable_crud` menjadi `true`.

Konfigurasi base_url default: http://127.0.0.1:8000. Pilih No environment agar variabel lingkungan lain tidak menimpa alamat.
