<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keranjang', function (Blueprint $t) {
            $t->increments('id_keranjang');
            $t->unsignedInteger('id_pelanggan'); $t->unsignedInteger('id_produk');
            $t->unsignedInteger('jumlah')->default(1);
            $t->unique(['id_pelanggan', 'id_produk']);
            $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
            $t->foreign('id_produk')->references('id_produk')->on('produk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang');
    }
};
