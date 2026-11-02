<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', fn (Blueprint $t) => $t->string('email', 120)->nullable());
        Schema::create('alamat', function (Blueprint $t) {
            $t->increments('id_alamat');
            $t->unsignedInteger('id_pelanggan');
            $t->string('label', 40);
            $t->string('nama_penerima', 100);
            $t->string('no_telp', 16);
            $t->string('alamat');
            $t->string('kota', 100);
            $t->string('kode_pos', 10);
            $t->boolean('utama')->default(false);
            $t->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat');
        Schema::table('pelanggan', fn (Blueprint $t) => $t->dropColumn('email'));
    }
};
