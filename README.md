# BUMKam Tuah Rezeki

Aplikasi web untuk membantu operasional BUMKam Tuah Rezeki, mulai dari pengelolaan pelanggan dan persediaan barang sampai pemesanan, pembayaran, pemantauan kredit, dan laporan.

## Gambaran proyek

Proyek ini adalah aplikasi administrasi BUMKam yang dibangun dengan Laravel. Aplikasi menyimpan data operasional dalam database dan menyediakan halaman kerja untuk Staff serta halaman pemantauan dan persetujuan untuk Pimpinan.

### Fitur yang sudah dibuat

- **Dashboard** — ringkasan pelanggan, barang, pemesanan, pembayaran, kondisi kredit, dan informasi operasional. Tampilan dashboard menyesuaikan peran pengguna.
- **Pelanggan** — pencatatan dan pengelolaan identitas serta informasi kontak, termasuk foto KTP dan pencarian pelanggan.
- **Barang dan stok** — pengelolaan katalog barang dan jumlah stok. Stok diperiksa saat pesanan dibuat, dikurangi ketika barang dipesan, dan disesuaikan kembali pada kondisi pembatalan atau penolakan pesanan.
- **Pemesanan** — pencatatan pelanggan, barang, jumlah, harga, tanggal, skema pembayaran, uang muka, tenor, bunga, angsuran, serta data jaminan kredit. Pimpinan dapat menyetujui atau menolak pengajuan dan memberikan alasan penolakan.
- **Pembayaran** — pencatatan pembayaran dan bukti pembayaran. Pembayaran dapat dicatat sebagai tunai/di tempat atau dilakukan secara online melalui Midtrans Snap. Status online diperbarui melalui notifikasi gateway yang tervalidasi.
- **Monitoring kredit** — perhitungan total kewajiban, jumlah terbayar, sisa kewajiban, jadwal jatuh tempo, dan status angsuran. Pembayaran online baru dihitung setelah berstatus `settlement`.
- **Jaminan** — penyimpanan dokumen jaminan secara privat, pemeriksaan dan persetujuan Pimpinan, serta pencatatan pengembalian jaminan setelah kewajiban kredit lunas.
- **Laporan** — ringkasan dan rincian transaksi untuk membantu pemantauan kegiatan usaha.
- **Pengguna dan hak akses** — pengelolaan akun Staff oleh Pimpinan, termasuk status aktif dan reset kata sandi. Akun nonaktif tidak dapat masuk.
- **Audit Trail** — pencatatan login/logout dan aktivitas perubahan data untuk membantu penelusuran aktivitas aplikasi.
- **Antarmuka operasional** — dashboard, navigasi, tabel data, form, detail, dan aksi dibuat dengan Blade serta komponen gaya Tailwind CSS/Bootstrap. Halaman detail dan edit memakai tampilan yang lebih ringkas, sedangkan aksi tabel menggunakan ikon yang konsisten.

### Alur kerja utama

1. Staff memasukkan data pelanggan dan barang, termasuk stok yang tersedia.
2. Staff membuat pemesanan dan memilih skema pembayaran. Pesanan kredit dapat menyertakan uang muka, tenor, serta dokumen jaminan.
3. Pimpinan memeriksa pengajuan kredit, lalu menyetujui atau menolaknya. Pengajuan yang ditolak menyimpan alasan keputusan.
4. Pembayaran dicatat secara tunai/di tempat atau diproses melalui Midtrans. Status pembayaran online mengikuti notifikasi resmi dari Midtrans.
5. Monitoring kredit menyusun jadwal angsuran dan menghitung sisa kewajiban berdasarkan pembayaran yang telah terverifikasi.
6. Setelah kewajiban lunas, pengembalian jaminan dapat dicatat. Dashboard, laporan, dan Audit Trail membantu pemantauan aktivitas.

### Pengguna aplikasi

- **Staff** mengelola data operasional seperti pelanggan, barang, pemesanan, dan pembayaran sesuai aturan aplikasi.
- **Pimpinan** memantau dashboard, laporan, kredit, dan audit; memeriksa jaminan; menyetujui pengajuan pemesanan; serta mengelola akun Staff. Akses perubahan data operasional dibatasi di backend, bukan hanya dengan menyembunyikan tombol.

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
| Logo dan warna brand BUMKam | `public/assets/img/bumkam-mark.png`, `resources/css/app.css`, `resources/views/login.blade.php` |
| Sidebar/layout lama atau pendukung | `resources/views/dashboard-layout.blade.php`, `resources/views/layouts/app.blade.php` |
| Pelanggan | `app/Http/Controllers/PelangganController.php`, `resources/views/pelanggan/` |
| Barang dan stok | `app/Http/Controllers/BarangController.php`, `resources/views/barang/` |
| Pemesanan | `app/Http/Controllers/PemesananController.php`, `resources/views/pemesanan/` |
| Pembayaran dan Midtrans | `app/Http/Controllers/PembayaranController.php`, `resources/views/pembayaran/` |
| Monitoring kredit | `app/Http/Controllers/MonitoringKreditController.php`, `app/Services/MonitoringKreditService.php`, `resources/views/monitoring-kredit/` |
| Laporan | `app/Http/Controllers/LaporanController.php`, `resources/views/laporan/index.blade.php` |
| Middleware akses Pimpinan | `app/Http/Middleware/PimpinanAccess.php`, didaftarkan di `bootstrap/app.php` |
| Dokumen jaminan privat | `app/Models/Jaminan.php`, `app/Http/Controllers/JaminanController.php`, `resources/views/jaminan/`, migration `2026_10_31_000001_create_jaminan_table.php` |
| Audit Trail | `app/Models/AuditTrail.php`, `app/Services/AuditTrailWriter.php`, `app/Http/Middleware/RecordAuditTrail.php`, `app/Http/Controllers/AuditTrailController.php`, `resources/views/audit-trail/` |
| Skema database | `database/migrations/` |

Model utama menggunakan nama tabel dan primary key khusus (misalnya `Pelanggan` memakai tabel `pelanggan` dan primary key string `id_pelanggan`). Periksa model serta migration sebelum mengubah query atau struktur data.

Logo BUMKam dipakai di halaman login dan sidebar. Warna identitas utamanya hijau tua dan hijau daun; pertahankan aset logo serta aksen tersebut ketika melakukan perubahan UI.

## Hak akses

- Route operasional dilindungi oleh middleware `auth` dan `pimpinan.access`.
- Pimpinan hanya dapat melihat Dashboard, Laporan, daftar/detail Pelanggan dan Barang, Monitoring Kredit, serta dokumen Jaminan; Pimpinan dapat memverifikasi jaminan, melihat Audit Trail, dan mengelola akun Staff. Operasi data usaha lainnya tetap mengikuti pemeriksaan di backend.
- Pimpinan tidak boleh membuat/mengubah/menghapus data Pelanggan dan Barang, Pemesanan, atau Pembayaran. Tindakan verifikasi Jaminan dan pengelolaan akun Staff adalah pengecualian yang disediakan khusus.
- Menu yang disembunyikan di Blade bukan pengganti pemeriksaan akses backend. Periksa `PimpinanAccess` dan validasi/otorisasi controller ketika mengubah hak akses.
- Role yang dipakai adalah nilai `peran` pada pengguna, antara lain `Staff` dan `Pimpinan`.

## Dokumen jaminan kredit

Dokumen jaminan (BPKB atau Sertifikat Tanah) diinput saat Staff membuat pengajuan pada form Pemesanan dan disimpan sebagai satu record yang terhubung ke pemesanan. Perbaikan informasi atau penggantian file dilakukan dari menu Manajemen Jaminan, yang menggunakan record yang sama. Dokumen disimpan pada disk `local` Laravel dengan root `storage/app/private`; jangan buat symbolic link ke folder `public`. Pimpinan dapat melihat dokumen dan menyetujui atau menolak dengan alasan. Keputusan menyimpan identitas Pimpinan serta waktu verifikasi. Staff hanya dapat mencatat pengembalian dokumen jika seluruh kewajiban sudah lunas. File valid: JPG, PNG, WEBP, PDF, maksimum 5 MB. Seluruh akses file melalui route terautentikasi `jaminan.document`.

## Akun pengguna dan Audit Trail

Migration `2026_10_31_000002_add_is_active_to_pengguna_table.php` menambahkan status akun dan default akun lama menjadi aktif. Pengguna yang nonaktif tidak dapat login. Menu Pengguna dan Audit Trail dibatasi untuk Pimpinan; Pimpinan dapat menambah akun Staff serta memperbarui nama, username, status, dan melakukan reset password. Role tetap hanya `Staff` dan `Pimpinan`, dan akun Pimpinan tidak dapat dikelola dari form Staff. Password baru di-hash menggunakan `Hash::make()` dan tidak pernah diperlihatkan kembali.

Audit Trail dibuat oleh middleware untuk login/logout dan aksi tulis di route aplikasi. Halaman Pimpinan menyediakan filter pengguna, jenis aktivitas, rentang tanggal, dan pencarian. Tabel log tidak memiliki operasi edit/hapus di aplikasi. Nilai form tidak disimpan agar NIK, password, dan isi dokumen pribadi tidak masuk log; kolom nilai lama/baru dibiarkan kosong.

Setelah menarik perubahan terbaru, jalankan migration baru pada database lokal:

```powershell
php artisan migrate
```

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
