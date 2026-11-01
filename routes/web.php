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
use App\Http\Controllers\JaminanController;
use App\Http\Controllers\AuditTrailController;

Route::get('/', function () {
    return view('welcome');
});

// Login
Route::get('/login', [LoginController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.process')->middleware('audit.trail');

// Callback resmi gateway; validasi autentikasinya menggunakan signature Midtrans.
Route::post('/pembayaran/midtrans/notification', [PembayaranController::class, 'notifikasiMidtrans'])
    ->name('pembayaran.midtrans.notification')->middleware('audit.trail');

// Halaman yang harus login terlebih dahulu
Route::middleware(['auth', 'pimpinan.access', 'audit.trail'])->group(function () {

    // Pencarian pelanggan (untuk dropdown di form pemesanan).
    // WAJIB di atas Route::resource('pelanggan'), kalau tidak "cari"
    // akan dianggap sebagai {pelanggan} milik route show.
    Route::get('pelanggan/cari', [PelangganController::class, 'cari'])
        ->name('pelanggan.cari');

    Route::resource('pelanggan', PelangganController::class);

    Route::resource('barang', BarangController::class);

    Route::resource('pemesanan', PemesananController::class);
    Route::put('/pemesanan/{pemesanan}/approval', [PemesananController::class, 'approve'])
        ->name('pemesanan.approve');

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

    Route::get('/jaminan', [JaminanController::class, 'index'])->name('jaminan.index');
    Route::post('/jaminan', [JaminanController::class, 'store'])->name('jaminan.store');
    Route::get('/jaminan/{jaminan}', [JaminanController::class, 'show'])->name('jaminan.show');
    Route::get('/jaminan/{jaminan}/dokumen', [JaminanController::class, 'document'])->name('jaminan.document');
    Route::put('/jaminan/{jaminan}', [JaminanController::class, 'update'])->name('jaminan.update');
    Route::put('/jaminan/{jaminan}/verifikasi', [JaminanController::class, 'verify'])->name('jaminan.verify');
    Route::put('/jaminan/{jaminan}/pengembalian', [JaminanController::class, 'returnCollateral'])->name('jaminan.return');

    Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail.index');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');
});
