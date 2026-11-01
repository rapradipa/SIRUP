<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengiriman', function (Blueprint $t) {
            $t->string('jenis', 10)->default('kirim');          // kirim | ambil (ambil di toko)
            $t->string('nama_penerima', 100)->nullable();
            $t->string('no_telp_penerima', 16)->nullable();
            $t->string('kota', 100)->nullable();
            $t->string('kode_pos', 10)->nullable();
            $t->string('kurir', 60)->nullable();
            $t->string('no_resi', 40)->nullable();
            $t->date('estimasi_tiba')->nullable();
        });
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->timestamp('dibayar_pada')->nullable();
        });
        // Jejak audit perubahan status pengiriman (juga dipakai untuk "Riwayat Perjalanan")
        Schema::create('pengiriman_log', function (Blueprint $t) {
            $t->increments('id_log');
            $t->unsignedInteger('id_transaksi');
            $t->string('status', 30);
            $t->string('keterangan');
            $t->timestamp('dibuat_pada')->useCurrent();
            $t->foreign('id_transaksi')->references('id_transaksi')->on('transaksi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengiriman_log');
        Schema::table('pembayaran', fn (Blueprint $t) => $t->dropColumn('dibayar_pada'));
        Schema::table('pengiriman', fn (Blueprint $t) => $t->dropColumn(['jenis', 'nama_penerima', 'no_telp_penerima', 'kota', 'kode_pos', 'kurir', 'no_resi', 'estimasi_tiba']));
    }
};
