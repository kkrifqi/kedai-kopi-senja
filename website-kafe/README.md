# Kedai Kopi Senja

Sistem POS dan manajemen operasional Kedai Kopi Senja berbasis Laravel. Aplikasi menyediakan panel manajer menggunakan Filament PHP serta antarmuka kasir berbasis Laravel Blade dan Tailwind CSS.

## Fitur Utama

### Autentikasi Staf

- Satu halaman login staf di `/login`.
- Setelah login, Manajer diarahkan ke panel Filament di `/admin`, sedangkan Kasir diarahkan ke POS di `/kasir`.
- Middleware `CekPeran` membatasi akses halaman kasir berdasarkan role.
- Ganti password tersedia di `/ganti-password`. Proses ini memverifikasi username dan password lama sebelum menyimpan password baru dengan panjang minimal 8 karakter.
- Akses panel Filament dibatasi untuk pengguna dengan role Manajer.

### Modul Manajer — `/admin`

- Mengelola data bahan, menu, dan resep.
- Mencatat penerimaan stok bahan.
- Mengimpor data bahan, menu, dan resep melalui CSV.
- Menyinkronkan harga bahan dari layanan supplier yang dikonfigurasi.
- Melihat laporan melalui tiga tab:
  - **Penjualan per Menu:** jumlah porsi terjual dan pendapatan dari harga satuan yang direkam pada transaksi. Pesanan berstatus `Dibatalkan` dikecualikan.
  - **Bahan Menipis:** bahan dengan stok saat ini kurang dari atau sama dengan batas minimum, beserta status `Menipis` atau `Habis`.
  - **HPP & Margin per Menu:** kategori, harga jual, HPP, dan margin setiap menu.

### Modul Kasir — `/kasir`

- Membuat pesanan dari menu aktif yang memiliki resep dan stok bahan yang mencukupi.
- Memasukkan nomor meja 1–10 serta metode bayar `Tunai` atau `QRIS`.
- Melihat dan mencetak struk pesanan.
- Melihat daftar pesanan dan memfilternya berdasarkan status.
- Memproses pesanan melalui transisi status `Baru` → `Diproses` → `Selesai`.
- Membatalkan pesanan yang masih berstatus `Baru`; stok bahan dikembalikan sesuai resep.

## Aturan Bisnis Penting

- **Perhitungan total:** service charge sebesar 5% dari subtotal. Pajak sebesar 10% dihitung dari subtotal ditambah service charge. Total harga adalah subtotal + service charge + pajak.
- **Snapshot harga transaksi:** harga jual menu saat pesanan dibuat disimpan di `harga_satuan_terekam`. Laporan penjualan menggunakan harga tersebut, bukan harga menu terbaru.
- **Validasi dan pemotongan stok:** sistem mengakumulasi kebutuhan bahan dari semua item pesanan, mengunci baris bahan saat transaksi, memeriksa kecukupan stok, lalu mengurangi stok. Kegagalan validasi membatalkan transaksi.
- **Pembatalan pesanan:** hanya pesanan berstatus `Baru` yang dapat dibatalkan. Stok bahan dikembalikan berdasarkan resep menu pada detail pesanan.
- **HPP dan margin:** HPP dihitung sebagai jumlah takaran resep dikalikan harga bahan saat ini (`Σ takaran × harga_sekarang`). Margin dihitung sebagai harga jual dikurangi HPP.
- **Ketersediaan menu:** POS memeriksa status aktif, keberadaan resep, dan kecukupan stok bahan. Menu tanpa resep tidak dapat dipesan.

## Struktur Database

Tabel utama domain bisnis:

| Tabel | Keterangan |
|---|---|
| `users` | Akun staf dan role Manajer/Kasir. |
| `bahans` | Master bahan, satuan dasar, stok, batas minimum, dan harga saat ini. |
| `menus` | Master menu, kategori, harga jual, dan status aktif. |
| `reseps` | Relasi bahan dan takaran yang diperlukan untuk satu porsi menu. |
| `stok_masuks` | Pencatatan penerimaan stok oleh Manajer. |
| `detail_stok_masuks` | Rincian bahan dan jumlah pada setiap penerimaan stok. |
| `pesanans` | Data transaksi, meja, metode bayar, status, dan rincian nilai transaksi. |
| `detail_pesanans` | Item menu, jumlah porsi, dan snapshot harga satuan pada transaksi. |

Satuan dasar bahan yang digunakan adalah `g`, `ml`, dan `pcs`. Pesanan memiliki status `Baru`, `Diproses`, `Selesai`, atau `Dibatalkan`.

## Prasyarat

- PHP versi 8.3 atau lebih baru.
- Composer.
- MySQL atau MariaDB.
- Git.

## Instalasi Lokal

Contoh berikut menjalankan aplikasi dari folder `website-kafe` di dalam repository:

```bash
git clone https://github.com/kkrifqi/kedai-kopi-senja.git
cd kedai-kopi-senja/website-kafe
composer install
```

Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi aplikasi dan database:

```bash
cp .env.example .env
```

Atur koneksi database MySQL/MariaDB di `.env`, misalnya:

```dotenv
APP_NAME="Kedai Kopi Senja"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kedai_kopi_senja
DB_USERNAME=root
DB_PASSWORD=

PASSWORD_MANAJER=GANTI_DENGAN_PASSWORD_KUAT
PASSWORD_KASIR=GANTI_DENGAN_PASSWORD_KUAT
```

Buat database `kedai_kopi_senja` pada MySQL/MariaDB, kemudian jalankan:

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed --class=UserSeeder
php artisan serve
```

Aplikasi tersedia di `http://localhost:8000`.

> **Catatan seeding:** `UserSeeder` membuat akun staf dengan password dari `PASSWORD_MANAJER` dan `PASSWORD_KASIR`. Saat ini `DatabaseSeeder` masih menggunakan factory bawaan Laravel dan belum memanggil `UserSeeder`; factory tersebut tidak sesuai dengan kolom tabel `users` proyek ini. Karena itu, `php artisan migrate --seed` tidak dapat digunakan untuk setup saat ini. Jalankan migrasi dan `UserSeeder` secara terpisah seperti perintah di atas.

### Alternatif menyalin `.env` di PowerShell

Jika menggunakan Windows PowerShell, gunakan:

```powershell
Copy-Item .env.example .env
```

## Akun Demo / Default Login

Akun berikut dibuat oleh `UserSeeder`. Isi kolom password dengan nilai yang Anda atur sendiri pada `.env`; repository tidak menyediakan password default.

| Role | Username | Password |
|---|---|---|
| Manajer | `manajer` | `<isi sesuai PASSWORD_MANAJER di .env>` |
| Kasir | `kasir` | `<isi sesuai PASSWORD_KASIR di .env>` |

## Rencana Pengembangan Selanjutnya

- Menyediakan REST API untuk mendukung aplikasi mobile pelanggan.
- Mengembangkan lebih lanjut integrasi supplier; sinkronisasi harga bahan dari panel Manajer sudah tersedia.
