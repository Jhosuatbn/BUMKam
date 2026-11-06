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
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\MonitoringStokController;
use App\Http\Controllers\ReturPelangganController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\PengeluaranOperasionalController;

Route::get('/', function () {
    return view('public.home');
})->name('public.home');

Route::view('/unit-usaha', 'public.unit-usaha')->name('public.unit-usaha');
Route::view('/profil', 'public.profil')->name('public.profil');
Route::view('/berita', 'public.berita')->name('public.berita');

// Login
Route::get('/login', [LoginController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.process')->middleware(['throttle:5,1', 'audit.trail']);

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
    Route::get('/pelanggan/{pelanggan}/foto-ktp', [PelangganController::class, 'fotoKtp'])->name('pelanggan.foto-ktp');

    Route::resource('barang', BarangController::class);

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/supplier/create', [SupplierController::class, 'create'])->name('supplier.create');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::get('/supplier/{supplier}', [SupplierController::class, 'show'])->name('supplier.show');
    Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::get('/barang-masuk/create', [BarangMasukController::class, 'create'])->name('barang-masuk.create');
    Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
    Route::get('/barang-masuk/cari-barang', [BarangMasukController::class, 'cariBarang'])->name('barang-masuk.cari-barang');
    Route::get('/barang-masuk/{barangMasuk}', [BarangMasukController::class, 'show'])->name('barang-masuk.show');
    Route::get('/monitoring-stok', [MonitoringStokController::class, 'index'])->name('monitoring-stok.index');
    Route::get('/retur', [ReturPelangganController::class, 'index'])->name('retur.index');
    Route::get('/retur/create', [ReturPelangganController::class, 'create'])->name('retur.create');
    Route::post('/retur', [ReturPelangganController::class, 'store'])->name('retur.store');
    Route::get('/retur/{retur}', [ReturPelangganController::class, 'show'])->name('retur.show');
    Route::post('/retur/{retur}/keputusan', [ReturPelangganController::class, 'decide'])->name('retur.decide');
    Route::get('/stok-opname', [StokOpnameController::class, 'index'])->name('stok-opname.index');
    Route::get('/stok-opname/create', [StokOpnameController::class, 'create'])->name('stok-opname.create');
    Route::post('/stok-opname', [StokOpnameController::class, 'store'])->name('stok-opname.store');
    Route::get('/stok-opname/{stokOpname}', [StokOpnameController::class, 'show'])->name('stok-opname.show');
    Route::post('/stok-opname/{stokOpname}/keputusan', [StokOpnameController::class, 'decide'])->name('stok-opname.decide');
    Route::get('/pengeluaran', [PengeluaranOperasionalController::class, 'index'])->name('pengeluaran.index');
    Route::get('/pengeluaran/create', [PengeluaranOperasionalController::class, 'create'])->name('pengeluaran.create');
    Route::post('/pengeluaran', [PengeluaranOperasionalController::class, 'store'])->name('pengeluaran.store');

    Route::resource('pemesanan', PemesananController::class);
    Route::get('/persetujuan-pemesanan', [PemesananController::class, 'persetujuan'])->name('pemesanan.persetujuan');
    Route::get('/pemesanan/cari/pelanggan', [PemesananController::class, 'cariPelanggan'])->name('pemesanan.cari-pelanggan');
    Route::get('/pemesanan/cari/barang', [PemesananController::class, 'cariBarang'])->name('pemesanan.cari-barang');
    Route::put('/pemesanan/{pemesanan}/approval', [PemesananController::class, 'approve'])
        ->name('pemesanan.approve');
    Route::post('/pemesanan/{pemesanan}/penyerahan', [PemesananController::class, 'handover'])
        ->name('pemesanan.handover');
    Route::post('/pemesanan/{pemesanan}/pembatalan', [PemesananController::class, 'cancel'])
        ->name('pemesanan.cancel');

    Route::resource('pembayaran', PembayaranController::class);
    Route::get('/pembayaran/{pembayaran}/bukti', [PembayaranController::class, 'bukti'])->name('pembayaran.bukti');
    Route::get('/pembayaran/{pembayaran}/struk', [PembayaranController::class, 'receipt'])->name('pembayaran.receipt');
    Route::post('/pembayaran/{pembayaran}/reversal', [PembayaranController::class, 'reverse'])->name('pembayaran.reverse');
    Route::post('/pembayaran/{pembayaran}/sinkronkan-midtrans', [PembayaranController::class, 'sinkronkanMidtrans'])->name('pembayaran.sync-midtrans');

    Route::get('/pembayaran/{pembayaran}/online', [PembayaranController::class, 'lanjutOnline'])
        ->name('pembayaran.online');

    Route::resource('pengguna', PenggunaController::class);

    Route::get('/laporan', [\App\Http\Controllers\LaporanController::class, 'index'])
        ->name('laporan.index');

    Route::get('/monitoring-kredit', [MonitoringKreditController::class, 'index'])
        ->name('monitoring-kredit.index');
    Route::get('/monitoring-kredit/jadwal-angsuran', [MonitoringKreditController::class, 'jadwal'])
        ->name('monitoring-kredit.jadwal');
    Route::get('/monitoring-kredit/denda-keterlambatan', [MonitoringKreditController::class, 'denda'])
        ->name('monitoring-kredit.denda');
    Route::get('/monitoring-kredit/{pemesanan}', [MonitoringKreditController::class, 'show'])
        ->name('monitoring-kredit.show');

    Route::get('/jaminan', [JaminanController::class, 'index'])->name('jaminan.index');
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
