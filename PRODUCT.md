# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Admin operasional dan pimpinan BUMKam memakai dashboard yang sama. Admin mengelola pelanggan, barang, pemesanan, pembayaran, pengguna, dan laporan. Pimpinan memantau ringkasan kinerja.

## Product Purpose

Mendukung operasional dan pemantauan usaha BUMKam Tuah Rezeki melalui pengelolaan pelanggan, barang, pemesanan, pembayaran, pengguna, dan laporan.

## Positioning

Belum diputuskan; jangan mengarang pembeda atau klaim produk.

## Operating Context

Aplikasi web internal dengan area dashboard yang memerlukan autentikasi. Dashboard menampilkan hitungan dari data database dan menjadi pintu masuk ke modul operasional.

## Capabilities and Constraints

Modul yang tersedia: pelanggan, barang, pemesanan, pembayaran, pengguna, laporan, dan dashboard. Hitungan pelanggan, barang, pemesanan, serta pembayaran pada dashboard berasal dari database. Tabel transaksi pada implementasi saat ini berisi contoh statis; contoh tersebut tidak boleh disajikan seolah data operasional aktual. Definisi dan periode tiap metrik belum dikonfirmasi.

## Brand Commitments

Pertahankan nama BUMKam dan Tuah Rezeki. Pengguna memilih dashboard admin konvensional sebagai preferensi visual dan tidak menetapkan produk pembanding tertentu. Pengguna meminta redesign visual dashboard, bukan perubahan fitur atau aturan bisnis.

## Evidence on Hand

- `resources/views/dashboard.blade.php`: implementasi dashboard dan tautan modul.
- `app/Http/Controllers/DashboardController.php`: hitungan ringkasan yang diambil dari database.
- `routes/web.php`: modul terlindungi autentikasi.
- Tabel transaksi dashboard saat ini adalah data contoh statis, bukan bukti transaksi aktual.

## Product Principles

- Tampilkan data aktual dengan konteks yang cukup untuk ditafsirkan.
- Dukung pemantauan pimpinan dan akses kerja admin dalam satu dashboard.
- Gunakan label bahasa Indonesia yang tidak ambigu.
- Pertahankan jalur ke modul operasional yang tersedia.
- Utamakan kontras, struktur semantik, dan penggunaan yang nyaman di layar mobile.

## Accessibility & Inclusion

Prioritas yang dikonfirmasi untuk redesign ini adalah aksesibilitas visual; perhatikan kontras, keterbacaan, struktur semantik, dan responsivitas mobile. Standar formal yang diwajibkan belum ditentukan.
