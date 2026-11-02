<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KontakController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PengirimanController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\StokMasukController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TransaksiController;
use App\Http\Middleware\Peran;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Beranda terbuka untuk umum; hanya staf yang diarahkan ke dashboard admin
    return redirect()->route(auth()->user()?->isStaf() ? 'admin.dashboard' : 'beranda');
});

// Beranda pelanggan: tanpa login
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('/kontak', [KontakController::class, 'index'])->name('kontak');
Route::get('/katalog', [KatalogController::class, 'index'])->name('katalog');
Route::get('/produk/{id}', [KatalogController::class, 'show'])->whereNumber('id')->name('produk');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'show'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.proses');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.proses');
});

Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', Peran::class . ':pelanggan'])->group(function () {
    Route::get('/akun', [AkunController::class, 'profil'])->name('akun');
    Route::get('/akun/ubah', [AkunController::class, 'ubah'])->name('akun.ubah');
    Route::put('/akun/ubah', [AkunController::class, 'perbarui'])->name('akun.perbarui');
    Route::put('/akun/sandi', [AkunController::class, 'sandi'])->name('akun.sandi');
    Route::get('/akun/alamat', [AlamatController::class, 'index'])->name('akun.alamat');
    Route::get('/akun/alamat/baru', [AlamatController::class, 'baru'])->name('akun.alamat.baru');
    Route::post('/akun/alamat', [AlamatController::class, 'simpan'])->name('akun.alamat.simpan');
    Route::get('/akun/alamat/{id}/ubah', [AlamatController::class, 'ubah'])->whereNumber('id')->name('akun.alamat.ubah');
    Route::put('/akun/alamat/{id}', [AlamatController::class, 'perbarui'])->whereNumber('id')->name('akun.alamat.perbarui');
    Route::post('/akun/alamat/{id}/utama', [AlamatController::class, 'utama'])->whereNumber('id')->name('akun.alamat.utama');
    Route::delete('/akun/alamat/{id}', [AlamatController::class, 'hapus'])->whereNumber('id')->name('akun.alamat.hapus');
    Route::get('/keranjang', [KeranjangController::class, 'index'])->name('keranjang');
    Route::post('/keranjang', [KeranjangController::class, 'tambah'])->name('keranjang.tambah');
    Route::post('/keranjang/hapus', [KeranjangController::class, 'hapus'])->name('keranjang.hapus');
    Route::patch('/keranjang/{id}', [KeranjangController::class, 'ubah'])->whereNumber('id')->name('keranjang.ubah');
    Route::delete('/keranjang/{id}', [KeranjangController::class, 'buang'])->whereNumber('id')->name('keranjang.buang');
    Route::post('/checkout', [KeranjangController::class, 'checkout'])->name('checkout');
    Route::get('/pembayaran/{id}', [PembayaranController::class, 'show'])->whereNumber('id')->name('pembayaran');
    Route::post('/pembayaran/{id}', [PembayaranController::class, 'bayar'])->whereNumber('id')->name('pembayaran.bayar');
    Route::post('/pembayaran/{id}/alamat', [PembayaranController::class, 'alamat'])->whereNumber('id')->name('pembayaran.alamat');
    Route::get('/pesanan', [PesananController::class, 'index'])->name('pesanan');
    Route::get('/pesanan/{id}', [PesananController::class, 'show'])->whereNumber('id')->name('pesanan.show');
    Route::get('/pesanan/{id}/invoice', [PesananController::class, 'invoice'])->whereNumber('id')->name('pesanan.invoice');
    Route::get('/pesanan/{id}/lacak', [PesananController::class, 'lacak'])->whereNumber('id')->name('pesanan.lacak');
    Route::post('/pesanan/{id}/terima', [PesananController::class, 'terima'])->whereNumber('id')->name('pesanan.terima');
    Route::get('/lacak', [PesananController::class, 'lacak'])->name('lacak');
});

Route::middleware(['auth', Peran::class . ':admin,kasir'])->prefix('admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('admin.transaksi');
    Route::get('/pengiriman', [PengirimanController::class, 'index'])->name('admin.pengiriman');
    Route::post('/pengiriman/{id}', [PengirimanController::class, 'ubah'])->whereNumber('id')->name('admin.pengiriman.ubah');
    Route::post('/transaksi/{id}/lunas', [TransaksiController::class, 'lunas'])->whereNumber('id')->name('admin.transaksi.lunas');
});

// Data master: khusus admin (kasir hanya transaksi, pembayaran, pengiriman, dan stok masuk)
Route::middleware(['auth', Peran::class . ':admin,kasir'])->prefix('admin')->group(function () {
    Route::get('/stok-masuk', [StokMasukController::class, 'index'])->name('admin.stok');
    Route::post('/stok-masuk', [StokMasukController::class, 'simpan'])->name('admin.stok.simpan');
});

Route::middleware(['auth', Peran::class . ':admin'])->prefix('admin')->group(function () {
    Route::get('/produk', [ProdukController::class, 'index'])->name('admin.produk');
    Route::get('/produk/baru', [ProdukController::class, 'baru'])->name('admin.produk.baru');
    Route::post('/produk', [ProdukController::class, 'simpan'])->name('admin.produk.simpan');
    Route::get('/produk/{id}/ubah', [ProdukController::class, 'ubah'])->whereNumber('id')->name('admin.produk.ubah');
    Route::put('/produk/{id}', [ProdukController::class, 'perbarui'])->whereNumber('id')->name('admin.produk.perbarui');
    Route::delete('/produk/{id}', [ProdukController::class, 'hapus'])->whereNumber('id')->name('admin.produk.hapus');

    Route::get('/kategori', [KategoriController::class, 'index'])->name('admin.kategori');
    Route::post('/kategori', [KategoriController::class, 'simpan'])->name('admin.kategori.simpan');
    Route::put('/kategori/{id}', [KategoriController::class, 'perbarui'])->whereNumber('id')->name('admin.kategori.perbarui');
    Route::delete('/kategori/{id}', [KategoriController::class, 'hapus'])->whereNumber('id')->name('admin.kategori.hapus');

    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('admin.pengguna');
    Route::get('/pengguna/baru', [PenggunaController::class, 'baru'])->name('admin.pengguna.baru');
    Route::post('/pengguna', [PenggunaController::class, 'simpan'])->name('admin.pengguna.simpan');
    Route::put('/pengguna/{id}/sandi', [PenggunaController::class, 'sandi'])->whereNumber('id')->name('admin.pengguna.sandi');
    Route::delete('/pengguna/{id}', [PenggunaController::class, 'hapus'])->whereNumber('id')->name('admin.pengguna.hapus');

    Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan');
    Route::get('/laporan/csv', [LaporanController::class, 'csv'])->name('admin.laporan.csv');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('admin.supplier');
    Route::get('/supplier/baru', [SupplierController::class, 'baru'])->name('admin.supplier.baru');
    Route::post('/supplier', [SupplierController::class, 'simpan'])->name('admin.supplier.simpan');
    Route::get('/supplier/{id}/ubah', [SupplierController::class, 'ubah'])->whereNumber('id')->name('admin.supplier.ubah');
    Route::put('/supplier/{id}', [SupplierController::class, 'perbarui'])->whereNumber('id')->name('admin.supplier.perbarui');
    Route::delete('/supplier/{id}', [SupplierController::class, 'hapus'])->whereNumber('id')->name('admin.supplier.hapus');
});
