<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $t) {
            $t->string('bank', 20)->nullable()->after('metode'); // bri | mandiri, diisi saat metode = transfer
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', fn (Blueprint $t) => $t->dropColumn('bank'));
    }
};
