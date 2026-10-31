<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringKreditController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\LoginController;

Route::get('/', function () {
    return view('welcome');
});

// Login
Route::get('/login', [LoginController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.process');

// Callback resmi gateway; validasi autentikasinya menggunakan signature Midtrans.
Route::post('/pembayaran/midtrans/notification', [PembayaranController::class, 'notifikasiMidtrans'])
    ->name('pembayaran.midtrans.notification');

// Halaman yang harus login terlebih dahulu
Route::middleware(['auth', 'pimpinan.access'])->group(function () {

    // Pencarian pelanggan (untuk dropdown di form pemesanan).
    // WAJIB di atas Route::resource('pelanggan'), kalau tidak "cari"
    // akan dianggap sebagai {pelanggan} milik route show.
    Route::get('pelanggan/cari', [PelangganController::class, 'cari'])
        ->name('pelanggan.cari');

    Route::resource('pelanggan', PelangganController::class);

    Route::resource('barang', BarangController::class);

    Route::resource('pemesanan', PemesananController::class);

    Route::resource('pembayaran', PembayaranController::class);

    Route::get('/pembayaran/{pembayaran}/online', [PembayaranController::class, 'lanjutOnline'])
        ->name('pembayaran.online');

    Route::resource('pengguna', PenggunaController::class);

    Route::get('/laporan', [\App\Http\Controllers\LaporanController::class, 'index'])
        ->name('laporan.index');

    Route::get('/monitoring-kredit', [MonitoringKreditController::class, 'index'])
        ->name('monitoring-kredit.index');
    Route::get('/monitoring-kredit/{pemesanan}', [MonitoringKreditController::class, 'show'])
        ->name('monitoring-kredit.show');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
