# BUMKam Tuah Rezeki

Aplikasi web untuk membantu operasional BUMKam Tuah Rezeki, mulai dari pengelolaan pelanggan dan persediaan barang sampai pemesanan, pembayaran, pemantauan kredit, dan laporan.

## Gambaran proyek

Proyek ini adalah aplikasi administrasi BUMKam yang dibangun dengan Laravel. Aplikasi menyimpan data operasional dalam database dan menyediakan halaman kerja untuk Staff serta halaman pemantauan dan persetujuan untuk Pimpinan.

### Fitur yang sudah dibuat

- **Dashboard** — ringkasan pelanggan, barang, pemesanan, pembayaran, kondisi kredit, dan informasi operasional. Tampilan dashboard menyesuaikan peran pengguna.
- **Pelanggan** — pencatatan dan pengelolaan identitas serta informasi kontak, termasuk foto KTP dan pencarian pelanggan.
- **Barang, supplier, dan stok** — pengelolaan katalog barang, supplier, penerimaan barang, riwayat pergerakan, pemantauan stok, pengajuan retur, dan stok opname. Retur serta selisih stok opname harus disetujui Pimpinan dan dicatat melalui riwayat pergerakan stok.
- **Pemesanan** — pencatatan pelanggan, barang, jumlah, harga, tanggal, skema pembayaran, uang muka, tenor, bunga, angsuran, serta data jaminan kredit. Pimpinan dapat menyetujui atau menolak pengajuan dan memberikan alasan penolakan.
- **Pembayaran** — pencatatan pembayaran dan bukti pembayaran, termasuk pratinjau file sebelum disimpan. Pembayaran dapat dicatat secara langsung atau dilakukan secara online melalui Midtrans Snap. Status online diperbarui melalui notifikasi gateway yang tervalidasi; transaksi settlement dihitung diterima dan refund mengurangi penerimaan serta alokasi secara tercatat.
- **Monitoring kredit dan denda** — daftar kredit, detail kewajiban, jadwal angsuran, status jatuh tempo, alokasi pembayaran ke pokok dan denda, serta daftar denda. Pengurangan denda oleh Pimpinan wajib memiliki alasan dan tercatat pada Audit Trail.
- **Pengingat tagihan WhatsApp** — pengiriman pengingat angsuran H-3 melalui Kirima untuk pelanggan yang menyetujui pesan WhatsApp, dengan catatan unik agar pengingat tidak terkirim dua kali.
- **Jaminan** — data jaminan terhubung dengan pemesanan, ditinjau dan disetujui/ditolak oleh Pimpinan, serta pengembalian dicatat setelah kredit lunas. Pratinjau dokumen tersedia pada halaman yang sama bila file jaminan tersedia di penyimpanan privat.
- **Laporan** — laporan pemesanan, kredit, penerimaan pembayaran, penerimaan barang, piutang, denda, persediaan, dan pengeluaran operasional. Laporan memiliki filter periode dan tata letak cetak.
- **Pengeluaran operasional** — pencatatan nomor transaksi, tanggal, kategori, keterangan, nominal, serta pengguna pencatat. Histori pengeluaran tidak dihapus dari halaman operasional.
- **Pengguna dan hak akses** — Pimpinan mengelola akun Staff, termasuk status aktif dan reset kata sandi. ID akun dibuat otomatis; akun nonaktif tidak dapat masuk.
- **Audit Trail** — pencatatan login/logout dan aktivitas perubahan data untuk membantu penelusuran aktivitas aplikasi.
- **Antarmuka operasional** — dashboard, navigasi, tabel, form, halaman detail, dan aksi dibuat dengan Blade serta gaya Tailwind CSS/Bootstrap. Form unggah foto KTP dan bukti pembayaran menampilkan pratinjau; pratinjau gambar/PDF dapat dibuka tanpa meninggalkan halaman. Tabel dan laporan disusun agar mudah dibaca dan dicetak.

### Catatan pengerjaan antarmuka

- Susunan isian pelanggan menempatkan NIK dan nama berdampingan, diikuti nomor telepon, foto KTP, lalu alamat. Urutan ini dipakai pada form tambah dan edit.
- Foto KTP dan bukti pembayaran menampilkan nama file, pratinjau gambar atau PDF, dan tombol untuk menghapus pilihan sebelum disimpan.
- Pencarian barang pada penerimaan stok memakai saran setelah tiga karakter. Nama barang dengan harga yang sama digabung menjadi satu hasil; harga berbeda tetap menjadi pilihan terpisah.
- Baris barang masuk dibuat otomatis saat nama barang, jumlah, dan harga satuan sudah diisi. Harga ditampilkan dalam format Rupiah dengan pemisah ribuan.
- Form tambah/edit, tombol aksi, konfirmasi hapus, halaman detail, serta tab persetujuan dirapikan dengan gaya dan warna yang konsisten. Tombol tema menampilkan satu ikon untuk beralih antara mode terang dan gelap.
- Tampilan Pimpinan pada daftar barang menyediakan aksi lihat saja. Status persetujuan pemesanan dipisah menjadi tab menunggu, disetujui, dan ditolak; hak akses tetap diperiksa di backend.
- Filter Audit Trail disusun mendatar pada layar lebar. Laporan kredit dirapikan untuk pembacaan dan pencetakan serta menampilkan nama pelanggan dan barang.

### Alur kerja aplikasi

#### Alur operasional Staff

1. Catat pelanggan beserta NIK, nama, kontak, alamat, dan foto KTP bila tersedia.
2. Catat barang yang dijual. Untuk mengisi stok, catat supplier dan penerimaan barang agar perubahan stok masuk ke riwayat pergerakan.
3. Buat pemesanan dengan memilih pelanggan, barang, jumlah, dan skema pembayaran. Untuk kredit, masukkan uang muka, tenor, dan informasi jaminan. Nomor jaminan berarti nomor resmi yang tercetak pada dokumen.
4. Pengajuan kredit berstatus menunggu persetujuan sampai diperiksa Pimpinan. Staff dapat melihat status serta alasan penolakan.
5. Catat pembayaran langsung atau arahkan pelanggan ke pembayaran online. Pembayaran dapat sebagian atau penuh; bukti pembayaran dapat dilampirkan.
6. Pantau sisa pokok, jadwal, status angsuran, dan denda di Monitoring Kredit. Setelah kewajiban lunas, catat pengembalian jaminan.
7. Jika pelanggan mengembalikan barang, ajukan retur untuk pemesanan yang sudah diserahkan, isi jumlah dan kondisi, lalu tunggu keputusan Pimpinan. Barang rusak atau belum diperiksa tidak otomatis ditambahkan ke stok.
8. Untuk pemeriksaan persediaan, catat jumlah fisik pada Stok Opname. Pimpinan menyetujui atau menolak; persetujuan menerapkan selisih sebagai pergerakan stok. Jika stok berubah sejak penghitungan, opname harus dihitung ulang.
9. Catat pengeluaran operasional dengan tanggal, kategori, keterangan, dan nominal; gunakan laporan Pengeluaran untuk meninjau data menurut periode.

#### Alur persetujuan Pimpinan

1. Buka daftar persetujuan dan pilih pengajuan yang menunggu.
2. Periksa rincian pelanggan, barang, nilai, tenor, angsuran, dan informasi jaminan.
3. Setujui pengajuan atau tolak dengan alasan. Keputusan tersimpan bersama identitas Pimpinan dan waktu proses.
4. Pantau pembayaran, jadwal kredit, dan laporan. Jika diperlukan, setujui pengurangan denda dengan nominal serta alasan; sistem menyimpan riwayat dan Audit Trail.
5. Tinjau jaminan. Pimpinan hanya melakukan verifikasi, bukan mengedit atau menghapus data jaminan.

#### Alur pembayaran, angsuran, dan denda

1. Pembayaran online berstatus `pending` belum dihitung sebagai uang yang diterima. Sistem menghitungnya setelah notifikasi Midtrans yang sah menetapkan status `settlement`; refund parsial mengurangi saldo pembayaran sesuai alokasi yang dibalik.
2. Pembayaran dialokasikan ke sisa pokok angsuran dan, bila ada, denda terkait. Pembayaran sebagian mengurangi sisa angsuran; pokok yang sudah dibayar tidak menjadi dasar denda.
3. Denda berjalan dihitung setelah masa tenggang tujuh hari sejak jatuh tempo: `sisa angsuran × 0,1% × hari terlambat`, dengan batas maksimal 5% dari sisa angsuran. Denda tidak berbunga dan tidak dikenakan pada angsuran yang lunas.
4. Pimpinan dapat mengurangi atau membebaskan denda dengan alasan wajib. Pengurangan tidak menghapus catatan denda awal maupun keputusan terdahulu.
5. Monitoring Kredit dan Laporan menampilkan pokok serta denda secara terpisah agar pendapatan denda tidak tercampur dengan angsuran pokok.

#### Alur data dan pemantauan

Data pelanggan, pemesanan, pembayaran, stok, dan jaminan saling terhubung melalui ID masing-masing. Dashboard menyajikan ringkasan, laporan membantu peninjauan transaksi dan kredit, sedangkan Audit Trail mencatat aktivitas penting untuk pelacakan.

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

### Pengingat angsuran lewat Kirima

Pengingat dijadwalkan pukul 09.00 waktu `KREDIT_TIMEZONE` (default `Asia/Jakarta`), tiga hari sebelum angsuran jatuh tempo. Pengiriman hanya mencakup kredit yang disetujui dan pelanggan yang memberi persetujuan pada halaman tambah/edit pelanggan. Semua data pelanggan lama secara default tidak ikut menerima pesan.

1. Terapkan migration baru: `php artisan migrate`.
2. Tambahkan `KIRIMA_CLIENT_ID`, `KIRIMA_CLIENT_SECRET`, dan `KIRIMA_DEVICE_JID` ke `.env` lokal. Gunakan nilai `device_jid` persis dari `GET /api/v1/wa/devices` (key perlu scope `wa.read`) atau detail perangkat di dashboard. Jangan menebak format JID. Lalu jalankan `php artisan config:clear`. Jangan mengirim atau memasukkan secret ke Git. Biarkan `KIRIMA_ENABLED=false` saat menyiapkan data.
3. Tandai persetujuan WhatsApp hanya pada pelanggan yang memang telah setuju menerima pengingat.
4. Simulasikan pemilihan angsuran tanpa mengirim pesan: `php artisan kirima:remind-installments --dry-run`.
5. Uji koneksi tanpa mengirim: `php artisan kirima:test 6281234567890`. Untuk mengirim satu pesan uji ke nomor Anda sendiri, set sementara `KIRIMA_ENABLED=true`, lalu jalankan `php artisan kirima:test 6281234567890 --send`. Setelah selesai, set kembali ke `false`.
6. Untuk uji pengingat, pastikan hanya pelanggan uji yang menyetujui WA dan jatuh temponya tiga hari lagi, lalu jalankan `php artisan kirima:remind-installments`. Setelah uji selesai, set `KIRIMA_ENABLED=false` sampai siap operasi.
7. Untuk jadwal otomatis, pastikan scheduler Laravel berjalan setiap menit. Pada server Linux, tambahkan cron `* * * * * cd /path/ke/aplikasi && php artisan schedule:run >> /dev/null 2>&1`. Pada pengembangan lokal, jalankan `php artisan schedule:work`.

Perintah harian hanya memproses angsuran yang jatuh tempo tepat tiga hari lagi dan belum lunas. Hasil pengiriman dicatat pada tabel `pengingat_tagihan`; kegagalan tersimpan untuk ditinjau di log aplikasi. Kirima menggunakan perangkat tertaut WhatsApp dan bukan WhatsApp Business Platform resmi; gunakan pengingat yang diharapkan penerima dan kirim secara wajar.

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
| Barang dan stok | `app/Http/Controllers/BarangController.php`, `app/Http/Controllers/MonitoringStokController.php`, `resources/views/barang/`, `resources/views/monitoring-stok/` |
| Supplier dan penerimaan barang | `app/Http/Controllers/SupplierController.php`, `app/Http/Controllers/BarangMasukController.php`, `app/Services/StockMovementService.php`, `resources/views/supplier/`, `resources/views/barang-masuk/` |
| Retur pelanggan dan stok opname | `app/Http/Controllers/ReturPelangganController.php`, `app/Http/Controllers/StokOpnameController.php`, `app/Services/StockMovementService.php`, `resources/views/retur/`, `resources/views/stok-opname/` |
| Pengeluaran operasional | `app/Http/Controllers/PengeluaranOperasionalController.php`, `resources/views/pengeluaran/` |
| Pemesanan | `app/Http/Controllers/PemesananController.php`, `resources/views/pemesanan/` |
| Pembayaran dan Midtrans | `app/Http/Controllers/PembayaranController.php`, `resources/views/pembayaran/` |
| Monitoring kredit dan denda | `app/Http/Controllers/MonitoringKreditController.php`, `app/Services/MonitoringKreditService.php`, `app/Services/DendaKeterlambatanService.php`, `app/Services/AlokasiPembayaranService.php`, `resources/views/monitoring-kredit/` |
| Laporan | `app/Http/Controllers/LaporanController.php`, `resources/views/laporan/index.blade.php` |
| Middleware akses Pimpinan | `app/Http/Middleware/PimpinanAccess.php`, didaftarkan di `bootstrap/app.php` |
| Jaminan | `app/Models/Jaminan.php`, `app/Http/Controllers/JaminanController.php`, `resources/views/jaminan/`, migration `2026_10_31_000001_create_jaminan_table.php` |
| Pratinjau lampiran | `resources/views/components/photo-preview-dialog.blade.php`, `resources/views/components/file-preview-dialog.blade.php`, `resources/js/app.js` |
| Audit Trail | `app/Models/AuditTrail.php`, `app/Services/AuditTrailWriter.php`, `app/Http/Middleware/RecordAuditTrail.php`, `app/Http/Controllers/AuditTrailController.php`, `resources/views/audit-trail/` |
| Skema database | `database/migrations/` |

Migration `2026_11_04_000004_add_returns_stocktakes_and_operating_expenses.php` menambahkan histori retur dan detail kondisi barang, pencatatan serta keputusan stok opname, dan pengeluaran operasional. Migration ini disiapkan sebagai perubahan tambahan; jangan menjalankannya sebelum meninjau status database dan mengikuti prosedur migrasi lingkungan aplikasi.

Model utama menggunakan nama tabel dan primary key khusus (misalnya `Pelanggan` memakai tabel `pelanggan` dan primary key string `id_pelanggan`). Periksa model serta migration sebelum mengubah query atau struktur data.

Logo BUMKam dipakai di halaman login dan sidebar. Warna identitas utamanya hijau tua dan hijau daun; pertahankan aset logo serta aksen tersebut ketika melakukan perubahan UI.

## Hak akses

- Route operasional dilindungi oleh middleware `auth` dan `pimpinan.access`.
- Pimpinan hanya dapat melihat Dashboard, Laporan, daftar/detail Pelanggan dan Barang, Monitoring Kredit, serta dokumen Jaminan; Pimpinan dapat memverifikasi jaminan, melihat Audit Trail, dan mengelola akun Staff. Operasi data usaha lainnya tetap mengikuti pemeriksaan di backend.
- Pimpinan tidak boleh membuat/mengubah/menghapus data Pelanggan dan Barang, Pemesanan, atau Pembayaran. Tindakan verifikasi Jaminan dan pengelolaan akun Staff adalah pengecualian yang disediakan khusus.
- Menu yang disembunyikan di Blade bukan pengganti pemeriksaan akses backend. Periksa `PimpinanAccess` dan validasi/otorisasi controller ketika mengubah hak akses.
- Role yang dipakai adalah nilai `peran` pada pengguna, antara lain `Staff` dan `Pimpinan`.

## Data jaminan kredit

Saat membuat pemesanan kredit, Staff mencatat jenis jaminan, nomor resmi dokumen, tanggal penyerahan, dan keterangan. Informasi itu terhubung ke pemesanan yang sama dan tidak meminta nomor pemesanan sebagai nomor dokumen. Form saat ini mencatat identitas jaminan dan tidak mengunggah foto/salinan dokumen pribadi. Halaman Manajemen Jaminan dipakai untuk melihat data, memverifikasi, memperbaiki identitas, dan mencatat pengembalian. Jika suatu record memiliki file di penyimpanan privat, file dilayani lewat route terautentikasi `jaminan.document` dan dapat dipratinjau di halaman detail. Pimpinan hanya memverifikasi; Staff dapat memperbaiki metadata dan mencatat pengembalian setelah lunas.

Foto KTP dan bukti pembayaran adalah lampiran berbeda yang disimpan pada disk privat Laravel. Akses lampiran lama maupun baru melalui route aplikasi yang terautentikasi; form menyediakan pratinjau sebelum simpan. Jangan mencampur lampiran tersebut dengan data jaminan.

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

## Backup, restore, dan dokumen pribadi

Foto KTP dan bukti pembayaran baru disimpan di `storage/app/private` dan ditayangkan lewat route login `pelanggan.foto-ktp` dan `pembayaran.bukti`. Dokumen jaminan juga dilayani lewat route terautentikasi `jaminan.document`. Jangan membuat symlink ke disk privat.

Panduan backup database dan storage untuk Windows/XAMPP serta Linux, termasuk uji restore pada database pengujian, tersedia di [docs/backup-restore.md](docs/backup-restore.md). Berkas KTP dan bukti lama dapat ditinjau menggunakan `php artisan documents:secure-legacy`; command menolak konflik checksum dan tidak menimpa file privat. Gunakan `--apply` hanya setelah backup terverifikasi.

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
