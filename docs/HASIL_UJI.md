# Hasil verifikasi Tahap 3

Tanggal: 11 November 2026 (WITA).

## Environment

Linux, PHP 8.4.27, Laravel Framework 13.42.0, PHPUnit 12.5.43, MariaDB 11.8.6 dengan driver Laravel mysql. HTTP diuji memakai server PHP lokal dan runner Postman Newman. Pengujian tidak dilakukan langsung pada Windows/Laragon milik pengguna (PHP 8.5.0).

## Ringkasan

| Pemeriksaan | Hasil |
| --- | --- |
| PHPUnit pada MariaDB | 24 tes lulus, 215 assertion |
| PHPUnit pada SQLite in-memory | 23 lulus, 1 dilewati, 214 assertion |
| Postman Collection melalui Newman/HTTP | 20 request, 45 assertion, 0 gagal |
| Script Postman | 0 kegagalan prerequest/test script |
| Laravel Pint | Lulus |
| Composer validate --strict --no-check-publish | Valid |
| Route API | 10 baris resource route, PUT/PATCH tersedia |
| Migration + seeder MariaDB | Berhasil |
| Cleanup Postman | Data kembali menjadi 2 kategori dan 1 buku seeder |

Tes khusus stok UNSIGNED dilewati pada SQLite karena SQLite tidak menegakkannya. Validasi HTTP stok negatif tetap diuji dan lulus pada kedua database. Konfigurasi MySQL pengujian memakai database disposable; file konfigurasi sementara tidak disertakan. Tes default memaksa SQLite in-memory agar tidak memakai database utama di .env.

Selain CRUD, suite menguji input kosong/duplikat/terlalu panjang, FK invalid, stok negatif/null/pecahan/melebihi kapasitas, tahun di luar batas, tanggal tidak valid, PATCH parsial, PUT yang kekurangan field utama, update ke nama kategori yang sudah dipakai, pemindahan kategori buku, perlindungan ID/timestamp, respons JSON 404 tanpa stack trace, serta konflik penghapusan kategori.

## Alur HTTP yang benar-benar dijalankan

| Request | HTTP aktual | Hasil assertion |
| --- | --- | --- |
| 01 — Status aplikasi | 200 | Lulus |
| 02 — Health Laravel | 200 | Lulus |
| 01 — Buat kategori uji | 201 | Lulus |
| 02 — Daftar kategori | 200 | Lulus |
| 03 — Detail kategori | 200 | Lulus |
| 04 — Ubah kategori | 200 | Lulus |
| 05 — Nama kategori duplikat ditolak | 422 | Lulus |
| 06 — Nama kategori kosong ditolak | 422 | Lulus |
| 07 — Buat buku uji | 201 | Lulus |
| 08 — Daftar buku | 200 | Lulus |
| 09 — Detail buku | 200 | Lulus |
| 10 — Ubah buku dan stok | 200 | Lulus |
| 11 — Kategori tidak valid ditolak | 422 | Lulus |
| 12 — Stok negatif ditolak | 422 | Lulus |
| 13 — Kategori yang dipakai tidak bisa dihapus | 409 | Lulus |
| 14 — Buku tetap ada dan stok tetap 8 | 200 | Lulus |
| 15 — Hapus buku uji | 200 | Lulus |
| 16 — Buku yang sudah dihapus menghasilkan 404 | 404 | Lulus |
| 17 — Hapus kategori uji setelah bukunya dihapus | 200 | Lulus |
| 18 — Kategori yang sudah dihapus menghasilkan 404 | 404 | Lulus |

## Batas bukti

PHPUnit memverifikasi aplikasi dan constraint pada lingkungan di atas. Newman benar-benar mengirim HTTP ke aplikasi yang terhubung ke MariaDB, bukan mock. MariaDB tidak membuktikan kompatibilitas seluruh versi MySQL; tetap cek konfigurasi port, password, ekstensi, dan database MOBILE_NPM di Laragon. Pengujian ini tidak mencakup beban tinggi atau request bersamaan.
