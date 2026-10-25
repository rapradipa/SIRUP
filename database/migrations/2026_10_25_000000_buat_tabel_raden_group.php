<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Nama tabel & kolom sama dengan raden_group.sql / ERD. Trigger & stored procedure
// digantikan logika Laravel (DB::transaction) pada tahap transaksi.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $t) {
            $t->increments('id_kategori'); $t->string('nama_kategori', 100);
        });
        Schema::create('supplier', function (Blueprint $t) {
            $t->increments('id_supplier'); $t->string('nama_supplier', 100);
            $t->string('alamat')->nullable(); $t->string('no_telpon', 15)->nullable();
        });
        Schema::create('karyawan', function (Blueprint $t) {
            $t->increments('id_karyawan'); $t->string('nama', 100);
            $t->string('alamat')->nullable(); $t->string('no_telp', 16)->nullable();
        });
        Schema::create('pelanggan', function (Blueprint $t) {
            $t->increments('id_pelanggan'); $t->string('nama', 100); $t->string('no_telp', 16)->nullable();
        });
        Schema::create('akun', function (Blueprint $t) {
            $t->increments('id_akun');
            $t->string('no_telp', 16)->unique();
            $t->string('password');
            $t->enum('role', ['pelanggan', 'kasir', 'admin'])->default('pelanggan');
            $t->unsignedInteger('id_pelanggan')->nullable(); $t->unsignedInteger('id_karyawan')->nullable();
            $t->rememberToken();
            $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
        });
        Schema::create('produk', function (Blueprint $t) {
            $t->increments('id_produk'); $t->string('nama_produk', 100);
            $t->decimal('harga', 10, 2); $t->integer('stok')->default(0);
            $t->unsignedInteger('id_kategori')->nullable(); $t->unsignedInteger('id_supplier')->nullable();
            $t->string('gambar', 120)->nullable(); $t->text('deskripsi')->nullable();
            $t->unsignedTinyInteger('diskon')->default(0);
            $t->foreign('id_kategori')->references('id_kategori')->on('kategori');
            $t->foreign('id_supplier')->references('id_supplier')->on('supplier');
        });
        Schema::create('transaksi', function (Blueprint $t) {
            $t->increments('id_transaksi'); $t->date('tanggal'); $t->decimal('total', 10, 2)->default(0);
            $t->unsignedInteger('id_pelanggan')->nullable(); $t->unsignedInteger('id_karyawan')->nullable();
            $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
            $t->foreign('id_karyawan')->references('id_karyawan')->on('karyawan');
        });
        Schema::create('detail_transaksi', function (Blueprint $t) {
            $t->increments('id_detail');
            $t->unsignedInteger('id_transaksi'); $t->unsignedInteger('id_produk');
            $t->integer('jumlah'); $t->decimal('subtotal', 10, 2);
            $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi');
            $t->foreign('id_produk')->references('id_produk')->on('produk');
        });
        Schema::create('pembayaran', function (Blueprint $t) {
            $t->increments('id_pembayaran'); $t->unsignedInteger('id_transaksi');
            $t->decimal('jumlah', 10, 2); $t->string('status_pembayaran', 50)->default('menunggu');
            $t->string('metode', 30)->nullable();
            $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi');
        });
        Schema::create('pengiriman', function (Blueprint $t) {
            $t->increments('id_pengiriman'); $t->unsignedInteger('id_transaksi');
            $t->string('alamat_pengiriman')->nullable(); $t->string('status_pengiriman', 50)->default('menunggu');
            $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi');
        });
        Schema::create('stok_masuk', function (Blueprint $t) {
            $t->increments('id_stok_masuk');
            $t->unsignedInteger('id_supplier'); $t->unsignedInteger('id_produk');
            $t->date('tanggal'); $t->integer('jumlah');
            $t->foreign('id_supplier')->references('id_supplier')->on('supplier');
            $t->foreign('id_produk')->references('id_produk')->on('produk');
        });
    }

    public function down(): void
    {
        foreach (['stok_masuk', 'pengiriman', 'pembayaran', 'detail_transaksi', 'transaksi', 'produk', 'akun', 'pelanggan', 'karyawan', 'supplier', 'kategori'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
