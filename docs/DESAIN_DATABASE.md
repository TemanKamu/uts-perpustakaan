# Desain database implementasi

Nama produksi lokal: `MOBILE_<NPM asli>`; NPM belum diberikan sehingga `.env.example` memakai `MOBILE_NPM` sebagai placeholder.

Satu kategori dapat memiliki nol atau banyak buku; setiap buku wajib mempunyai satu kategori. Kategori yang masih dipakai tidak dapat dihapus. Update ID kategori juga dibatasi dengan RESTRICT. Menghapus buku tidak menghapus kategori.

## tb_kategori

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id_kategori | BIGINT UNSIGNED | PK, auto increment |
| nama_kategori | VARCHAR(100) | NOT NULL, UNIQUE |

## tb_buku

| Kolom | Tipe | Aturan |
| --- | --- | --- |
| id_buku | BIGINT UNSIGNED | PK, auto increment |
| id_kategori | BIGINT UNSIGNED | NOT NULL, FK → tb_kategori.id_kategori |
| judul_buku | VARCHAR(255) | NOT NULL, boleh sama |
| nama_pengarang | VARCHAR(150) | NOT NULL |
| penerbit | VARCHAR(150) | Nullable |
| tahun_terbit | SMALLINT UNSIGNED | Nullable |
| cetakan | VARCHAR(50) | Nullable |
| tempat_terbit | VARCHAR(100) | Nullable |
| tgl_terima | DATE | Nullable |
| asal_buku | VARCHAR(100) | Nullable |
| jumlah_buku | INT UNSIGNED | NOT NULL, default 0 |
| keterangan | TEXT | Nullable |
| tgl_input | TIMESTAMP | NOT NULL, default CURRENT_TIMESTAMP, tidak auto-update |

Tidak ada `created_at`/`updated_at`; kedua model memakai `$timestamps = false`. `id_buku`, `id_kategori` sebagai PK masing-masing, dan `tgl_input` tidak masuk daftar fillable. Pada model Buku, FK `id_kategori` tetap fillable agar buku dapat dipindahkan ke kategori valid.

Semua tipe dan constraint di atas adalah adaptasi dari desain Tahap 1. Data contoh seeder bukan data penelitian. Nama kategori memakai collation `utf8mb4_unicode_ci` pada konfigurasi MySQL, sehingga pemeriksaan unik tidak membedakan kapitalisasi. Validasi HTTP sudah diimplementasikan pada Form Request Tahap 3; middleware Laravel memangkas whitespace dan mengubah string kosong menjadi null. Lihat `docs/API.md` untuk aturan input.
