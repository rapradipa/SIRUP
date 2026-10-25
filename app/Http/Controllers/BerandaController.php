<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    public function index()
    {
        return view('pelanggan.beranda', [
            'kategori' => DB::table('kategori')->orderBy('id_kategori')->limit(8)->get(),
            // Rekomendasi sementara: produk tersedia, yang berdiskon didahulukan
            'produk' => DB::table('produk')->where('stok', '>', 0)
                ->orderByDesc('diskon')->orderBy('id_produk')->limit(4)->get(),
        ]);
    }
}
