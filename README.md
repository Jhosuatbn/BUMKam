# BUMKam Tuah Rezeki

Aplikasi informasi untuk membantu operasional BUMKam Tuah Rezeki: data pelanggan dan barang, pemesanan, pembayaran, pemantauan kredit, serta laporan.

## Teknologi

- Laravel 12 dan PHP 8.2+
- MySQL (atau database lain yang didukung Laravel dan dikonfigurasi di `.env`)
- Blade, Tailwind CSS 4, Bootstrap 5, Vite
- ApexCharts untuk visualisasi dashboard
- Midtrans Snap untuk pembayaran online (opsional; Sandbox untuk uji coba)

## Persiapan lokal (Windows/XAMPP)

Pastikan PHP, Composer, Node.js/npm dan MySQL tersedia. Jalankan perintah dari folder proyek, misalnya `C:\xampp\htdocs\BUMKam`.

1. Pasang dependensi:

   ```powershell
   composer install
   npm install
   ```

2. Buat `.env` jika belum ada, lalu buat application key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   Jika `.env` sudah ada, jangan menimpanya; cukup pastikan `APP_KEY` sudah terisi.

3. Buat database kosong di MySQL (contoh: `bumkam`), lalu atur koneksi `.env`:

   ```dotenv
   APP_NAME="BUMKam Tuah Rezeki"
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bumkam
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Sesuaikan nama database dan kredensial dengan instalasi lokal. Jangan membagikan `.env` atau memasukkan key Midtrans ke Git.

4. Jalankan migrasi dan siapkan tautan file publik:

   ```powershell
   php artisan migrate
   php artisan storage:link
   ```

5. (Opsional, untuk akun demo) Buat akun Staff dan Pimpinan:

   ```powershell
   php artisan db:seed
   ```

   Seeder lokal saat ini membuat `admin` / `admin123` (Staff) dan `pimpinan` / `pimpinan123` (Pimpinan). Ini kredensial demo; ganti sebelum aplikasi dipakai di lingkungan nyata.

6. (Opsional, hanya database demo) Tambahkan data contoh dashboard:

   ```powershell
   php artisan db:seed --class=DemoDashboardSeeder
   ```

   Seeder ini membuat data contoh pelanggan, barang, pesanan, dan pembayaran. Jangan jalankan pada database produksi.

7. Jalankan Laravel dan Vite di dua terminal:

   ```powershell
   php artisan serve
   ```

   ```powershell
   npm run dev
   ```

   Buka `http://127.0.0.1:8000`. Untuk membuat aset siap deploy, gunakan `npm run build`.

> Perintah `migrate:fresh` menghapus seluruh tabel dan datanya. Jangan gunakan kecuali memang ingin menghapus dan membuat ulang database lokal.

## Bagian utama aplikasi

| Bagian | File penting |
| --- | --- |
| Route dan nama endpoint | `routes/web.php` |
| Dashboard Staff dan pemilihan dashboard | `app/Http/Controllers/DashboardController.php`, `resources/views/dashboard.blade.php`, `resources/views/dashboard-staff.blade.php` |
| Dashboard Pimpinan | `resources/views/dashboard-pimpinan.blade.php` |
| Layout navigasi utama | `resources/views/layouts/sneat-dashboard.blade.php` |
| Sidebar/layout lama atau pendukung | `resources/views/dashboard-layout.blade.php`, `resources/views/layouts/app.blade.php` |
| Pelanggan | `app/Http/Controllers/PelangganController.php`, `resources/views/pelanggan/` |
| Barang dan stok | `app/Http/Controllers/BarangController.php`, `resources/views/barang/` |
| Pemesanan | `app/Http/Controllers/PemesananController.php`, `resources/views/pemesanan/` |
| Pembayaran dan Midtrans | `app/Http/Controllers/PembayaranController.php`, `resources/views/pembayaran/` |
| Monitoring kredit | `app/Http/Controllers/MonitoringKreditController.php`, `app/Services/MonitoringKreditService.php`, `resources/views/monitoring-kredit/` |
| Laporan | `app/Http/Controllers/LaporanController.php`, `resources/views/laporan/index.blade.php` |
| Middleware akses Pimpinan | `app/Http/Middleware/PimpinanAccess.php`, didaftarkan di `bootstrap/app.php` |
| Skema database | `database/migrations/` |

Model utama menggunakan nama tabel dan primary key khusus (misalnya `Pelanggan` memakai tabel `pelanggan` dan primary key string `id_pelanggan`). Periksa model serta migration sebelum mengubah query atau struktur data.

## Hak akses

- Route operasional dilindungi oleh middleware `auth` dan `pimpinan.access`.
- Pimpinan hanya diizinkan membuka Dashboard, laporan, daftar/detail Pelanggan dan Barang (mode lihat), Monitoring Kredit, dan Logout.
- Tindakan menambah, mengubah, atau menghapus Pelanggan/Barang serta modul operasional lain tetap dilarang untuk Pimpinan di backend.
- Menu yang disembunyikan di Blade bukan pengganti pemeriksaan akses backend. Periksa `PimpinanAccess` dan validasi/otorisasi controller ketika mengubah hak akses.
- Role yang dipakai adalah nilai `peran` pada pengguna, antara lain `Staff` dan `Pimpinan`.

## Pembayaran Midtrans (opsional)

Pembayaran online menggunakan Midtrans Snap. Pembayaran tunai/lokal tetap dikelola melalui modul Pembayaran. Untuk Sandbox, isi key di `.env` (jangan tempel key ke README, source code, atau chat publik):

```dotenv
MIDTRANS_SERVER_KEY=isi_server_key_sandbox
MIDTRANS_CLIENT_KEY=isi_client_key_sandbox
MIDTRANS_IS_PRODUCTION=false
```

Setelah mengubah `.env`, jalankan:

```powershell
php artisan config:clear
```

Callback notifikasi terdaftar di `POST /pembayaran/midtrans/notification` dengan nama route `pembayaran.midtrans.notification`. Endpoint ini dikecualikan dari CSRF dan harus tetap memvalidasi signature Midtrans. Daftarkan URL publik yang dapat dijangkau Midtrans di pengaturan Notification URL, contohnya:

```text
https://<alamat-ngrok-aktif>/pembayaran/midtrans/notification
```

Alamat ngrok berubah atau tidak dapat dijangkau jika tunnel/server lokal mati. Pastikan tunnel meneruskan ke web server Laravel yang benar, dan untuk Apache/XAMPP arahkan document root ke folder `public` proyek. URL redirect setelah pembayaran hanya untuk navigasi; status pembayaran harus disinkronkan dari callback Midtrans yang valid. Cek endpoint dengan:

```powershell
php artisan route:list --path=pembayaran/midtrans/notification
```

Jika Midtrans menerima callback tetapi aplikasi tidak, periksa URL tunnel, tujuan forwarding, Apache/Laravel access log, dan halaman ngrok Inspector. Jangan menganggap redirect browser sebagai bukti pembayaran berhasil.

## Petunjuk untuk Codex atau kontributor berikutnya

Sebelum mengerjakan perubahan:

1. Baca README ini, cek `git status`, lalu periksa file terkait dan route/controller/model/migration yang benar-benar ada.
2. Jangan menghapus atau menimpa perubahan lokal yang belum di-commit. Jangan membaca atau menampilkan rahasia dari `.env`.
3. Pertahankan pola UI Sneat yang sudah dipakai oleh layout dan halaman aplikasi. Hindari memasang ulang template atau mengganti framework tanpa permintaan eksplisit.
4. Pastikan perubahan menu/hak akses memiliki pemeriksaan backend; jangan hanya menyembunyikan tombol di tampilan.
5. Ubah file yang berhubungan langsung dengan permintaan. Hindari mengubah dashboard Staff saat tugas hanya menyebut dashboard Pimpinan.
6. Jangan menebak nama kolom, route, relasi, atau variabel. Verifikasi dari kode dan migration sebelum menggunakannya.
7. Setelah perubahan, jelaskan file yang diubah dan dampaknya. Jalankan pemeriksaan yang sesuai dengan permintaan; hindari migrasi destruktif.

## Perintah bantu

```powershell
php artisan route:list
php artisan view:clear
php artisan config:clear
npm run build
```

Gunakan `php artisan view:cache` untuk memeriksa kompilasi Blade; bersihkan cache dengan `php artisan view:clear` jika tampilan lama masih muncul. Peringatan PHP atau Vite perlu dibedakan dari error yang benar-benar menghentikan proses.
