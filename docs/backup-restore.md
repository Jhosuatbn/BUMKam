# Backup dan Pemulihan BUMKam

Panduan ini melengkapi backup aplikasi dengan database dan seluruh berkas unggahan. Simpan backup **di luar document root aplikasi dan di lokasi terpisah dari server**. Jangan menaruhnya di `public`, `public/storage`, atau direktori yang dilayani web. Batasi akses backup karena isinya dapat memuat KTP, bukti pembayaran, dan dokumen jaminan.

## Data yang perlu dicadangkan

- Database MySQL `bumkam_db` (gunakan nama database dari konfigurasi lingkungan, tanpa menaruh kredensial di perintah atau repository).
- `storage/app/private` termasuk dokumen jaminan, KTP, dan bukti pembayaran yang diunggah setelah pembaruan keamanan.
- `storage/app/public` selama transisi, sampai seluruh berkas lama dipindahkan dan diverifikasi.
- Kode aplikasi dan berkas konfigurasi deployment yang tidak rahasia. Simpan rahasia deployment secara terpisah melalui pengelola rahasia.

Buat backup database dan berkas dalam jendela waktu yang sama. Untuk server aktif, hentikan sementara proses tulis atau gunakan snapshot filesystem/database yang konsisten.

## Windows / XAMPP

1. Buat folder backup di drive atau media terenkripsi yang tidak berada di dalam folder proyek, misalnya `E:\BUMKam-Backups\2026-11-05`.
2. Jalankan `mysqldump` dengan `-p` agar kata sandi diminta secara interaktif. Contoh dari PowerShell (sesuaikan lokasi XAMPP dan nama database):

   ```powershell
   & 'C:\xampp\mysql\bin\mysqldump.exe' --host=127.0.0.1 --user=bumkam_user --single-transaction --routines --triggers --databases bumkam_db -p --result-file='E:\BUMKam-Backups\2026-11-05\database.sql'
   ```

   Pastikan proses dump berhasil dan ukuran file masuk akal. Jangan menulis kata sandi langsung setelah `-p` atau menyimpannya dalam script.
3. Arsipkan folder `storage\app\private` dan, selama masa transisi, `storage\app\public` ke lokasi backup yang sama. Contoh:

   ```powershell
   Compress-Archive -Path 'C:\Users\ACER\Downloads\BUMKam\storage\app\private','C:\Users\ACER\Downloads\BUMKam\storage\app\public' -DestinationPath 'E:\BUMKam-Backups\2026-11-05\storage.zip'
   ```
4. Catat tanggal backup, versi kode/commit, ukuran file, dan hasil verifikasi checksum. Enkripsi dan batasi akses media backup.

## Linux

Gunakan direktori backup di luar web root dengan hak akses terbatas. Contoh berikut meminta kata sandi secara interaktif:

```bash
umask 077
mkdir -p /mnt/secure-backups/bumkam/2026-11-05
mysqldump --host=127.0.0.1 --user=bumkam_user --single-transaction --routines --triggers --databases bumkam_db -p > /mnt/secure-backups/bumkam/2026-11-05/database.sql
tar -czf /mnt/secure-backups/bumkam/2026-11-05/storage.tar.gz -C /srv/bumkam/storage/app private public
sha256sum /mnt/secure-backups/bumkam/2026-11-05/database.sql /mnt/secure-backups/bumkam/2026-11-05/storage.tar.gz > /mnt/secure-backups/bumkam/2026-11-05/SHA256SUMS
```

Ganti `/srv/bumkam` dengan lokasi deployment. Enkripsi backup sebelum dipindahkan ke lokasi off-site.

## Pemulihan dan uji restore

1. Pilih backup dan verifikasi checksum sebelum pemulihan.
2. Pulihkan **ke database dan direktori aplikasi pengujian**, bukan database produksi. Buat database kosong khusus pengujian terlebih dahulu.
3. Import dump pada Windows melalui Command Prompt atau terminal yang mendukung input file. Dump `--databases` menyertakan pembuatan/pemilihan database asal, jadi untuk pengujian ubah nama database pada salinan dump atau gunakan database uji bernama sama pada server uji yang terisolasi. Jangan mengimpor ke database produksi.
4. Ekstrak arsip storage ke `storage/app` pada salinan aplikasi uji. Pastikan `private` tidak menjadi symlink ke `public`.
5. Jalankan pemeriksaan aplikasi tanpa migration destruktif; login sebagai Staff dan Pimpinan, buka satu foto KTP, bukti pembayaran, dan dokumen jaminan yang memang berhak diakses.
6. Cocokkan beberapa jumlah transaksi dan berkas dengan catatan sebelum backup. Catat durasi pemulihan, kesalahan, serta langkah koreksi.
7. Hapus lingkungan uji dan salinan sementara secara aman setelah verifikasi. Jangan menguji restore dengan menimpa data operasional.

Jadwalkan backup sesuai toleransi kehilangan data operasional, simpan beberapa versi, dan lakukan uji restore berkala. Backup dianggap berhasil hanya setelah dump, arsip berkas, dan verifikasi restore tercatat.

## Pemindahan berkas lama dari disk publik

Unggahan KTP dan bukti pembayaran baru disimpan privat. Untuk berkas lama, buat backup terlebih dahulu, lalu tinjau daftar dry-run:

```bash
php artisan documents:secure-legacy
```

Jika daftar sudah diperiksa dan backup tersedia, pindahkan dengan opsi eksplisit:

```bash
php artisan documents:secure-legacy --apply
```

Perintah membandingkan checksum SHA-256 saat sumber dan tujuan tersedia. Tujuan privat yang berbeda tidak pernah ditimpa; konflik menghentikan pemindahan item tersebut dan mempertahankan sumber serta tujuan. File baru dibuat secara eksklusif agar proses bersamaan tidak dapat menimpa file tujuan. Sumber hanya dihapus setelah checksum sumber dan tujuan cocok. Jika sumber sudah tidak ada tetapi tujuan privat tersedia, pemanggilan ulang melaporkannya sebagai sudah privat tanpa menulis apa pun. Jangan menghapus backup selama pemeriksaan. Jalankan di maintenance window, lalu uji route dokumen melalui aplikasi dengan autentikasi. Jangan menjalankan `storage:link` untuk dokumen privat.

Folder legacy sensitif di `storage/app/public/pelanggan` dan `storage/app/public/bukti-pembayaran` memiliki `.htaccess` yang menolak akses langsung Apache. Jika deployment memakai Nginx, `.htaccess` tidak dibaca; tambahkan aturan setara sebelum mengaktifkan server, misalnya:

```nginx
location ^~ /storage/pelanggan/ { deny all; }
location ^~ /storage/bukti-pembayaran/ { deny all; }
```

Junction `public/storage` tetap digunakan untuk aset publik lain. Jangan simpan dokumen pribadi di sana.
