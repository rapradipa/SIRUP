<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori', function (Blueprint $t) {
            $t->string('ikon', 30)->nullable()->after('nama_kategori'); // kode ikon, lihat App\Support\IkonKategori::PILIHAN
        });

        // Isi awal sesuai ikon yang selama ini dipakai, supaya tampilan kategori lama tidak berubah.
        $awal = [1 => 'armchair', 2 => 'pot', 3 => 'home', 4 => 'lamp', 5 => 'monitor', 6 => 'bed', 7 => 'drop', 8 => 'wrench'];
        foreach ($awal as $id => $ikon) {
            DB::table('kategori')->where('id_kategori', $id)->whereNull('ikon')->update(['ikon' => $ikon]);
        }
    }

    public function down(): void
    {
        Schema::table('kategori', fn (Blueprint $t) => $t->dropColumn('ikon'));
    }
};
