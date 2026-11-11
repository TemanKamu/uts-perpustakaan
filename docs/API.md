# Kontrak API Perpustakaan

Base URL: `http://127.0.0.1:8000/api`. Gunakan `Accept: application/json` dan `Content-Type: application/json` untuk body JSON. API prefix dikonfigurasi pada `bootstrap/app.php`, sehingga `routes/api.php` tidak mengulang `/api`.

## Endpoint

| Method | Path | Sukses |
| --- | --- | --- |
| GET | /kategori | 200 |
| GET | /kategori/{id_kategori} | 200 |
| POST | /kategori | 201 |
| PUT / PATCH | /kategori/{id_kategori} | 200 |
| DELETE | /kategori/{id_kategori} | 200 |
| GET | /buku | 200 |
| GET | /buku/{id_buku} | 200 |
| POST | /buku | 201 |
| PUT / PATCH | /buku/{id_buku} | 200 |
| DELETE | /buku/{id_buku} | 200 |

Route model binding mencari PK `id_kategori`/`id_buku` dari model. Parameter URL ditulis `{kategori}`/`{buku}` di kode agar cocok dengan nama parameter controller. Nilainya tetap ID numerik sebenarnya.

## Respons

Sukses create/detail/update:

```json
{"message":"Kategori berhasil ditambahkan.","data":{"nama_kategori":"Teknologi","id_kategori":1}}
```

Daftar: `data` berupa array (kosong jika belum ada record), diurutkan berdasarkan PK. Daftar kategori menyertakan `buku_count`; detail kategori menyertakan array `buku`. Respons buku menyertakan objek `kategori`. Daftar belum memakai pagination karena scope dataset UTS kecil.

DELETE sukses: HTTP 200 dengan `message`, tanpa `data`.

- 422: kesalahan validasi, respons Laravel `message` dan `errors` per field.
- 404: `{"message":"Data atau endpoint tidak ditemukan."}` untuk ID maupun endpoint API yang tidak ada.
- 409: kategori masih dipakai buku; respons `message`, kategori/bukunya tidak dihapus.

## Body kategori

```json
{"nama_kategori":"Teknologi"}
```

Nama wajib string, tidak kosong setelah trim, maksimal 100 karakter, dan unik. Update boleh mempertahankan namanya sendiri. ID tidak boleh ditentukan lewat body.

## Body buku

```json
{
  "id_kategori": 1,
  "judul_buku": "Dasar-Dasar Komputer",
  "nama_pengarang": "Andi Pratama",
  "penerbit": "Pustaka Edukasi",
  "tahun_terbit": 2022,
  "cetakan": "Pertama",
  "tempat_terbit": "Jakarta",
  "tgl_terima": "2026-11-11",
  "asal_buku": "Pembelian",
  "jumlah_buku": 5,
  "keterangan": "Contoh input"
}
```

Ganti id_kategori dengan ID yang benar-benar ada; angka 1 di contoh bukan asumsi wajib.

POST dan PUT buku wajib menyertakan `id_kategori`, `judul_buku`, `nama_pengarang`. PATCH hanya memvalidasi field yang dikirim; field wajib yang dikirim tetap tidak boleh kosong/null. Field opsional yang tidak dikirim saat update mempertahankan nilai sebelumnya; kirim null untuk mengosongkan kolom nullable. Jadi PUT di API ini mensyaratkan tiga field utama, tanpa mereset kolom opsional yang dihilangkan.

Aturan teknis tambahan:

- FK kategori harus integer positif dan tersedia di database.
- Panjang teks mengikuti migration: judul 255, pengarang/penerbit 150, cetakan 50, tempat/asal 100.
- Tahun nullable; jika diisi, integer 4 digit, 1000 sampai tahun berjalan + 1. Ini keputusan implementasi, bukan ketentuan eksplisit jurnal.
- Tanggal terima nullable; jika diisi wajib tanggal valid dengan format YYYY-MM-DD.
- Jumlah buku integer 0–4294967295. Jika tidak diberikan pada create, database mengisi 0. Null tidak diterima.
- Keterangan nullable, maksimal 10000 karakter (batas aplikasi agar aman untuk TEXT utf8mb4).
- id_buku dan tgl_input dihasilkan sistem; input non-kosong untuk keduanya ditolak.
- Field tidak dikenal tidak diteruskan ke model karena controller hanya menyimpan hasil `validated()`.
- tgl_input tidak berubah saat update. Tanggal dan timestamp diserialisasi oleh Eloquent.

## Alur kode

`routes/api.php` memilih controller dan mengikat ID ke model. Form Request memeriksa data sebelum store/update dijalankan. Controller menyimpan data tervalidasi melalui Eloquent, lalu mengembalikan JsonResponse.

Penghapusan kategori memeriksa relasi lebih dahulu dan masih dilindungi FK RESTRICT. Penolakan FK MySQL saat delete diterjemahkan menjadi HTTP 409 jika ada request lain menyisipkan buku setelah pengecekan.

## Scope

API lokal untuk UTS, tanpa autentikasi sesuai rencana. Login, peminjaman, pengembalian, frontend, dan Android belum termasuk. Jangan membuka endpoint pengelolaan ini ke publik tanpa autentikasi.

Referensi teknis: https://laravel.com/docs/13.x/controllers dan https://laravel.com/docs/13.x/validation.
