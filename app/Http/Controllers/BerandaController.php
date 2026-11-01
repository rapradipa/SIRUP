<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class BerandaController extends Controller
{
    public function index()
    {
        return view('pelanggan.beranda', [
            'kategori' => DB::table('kategori')->orderBy('id_kategori')->limit(8)->get(),
            // Rekomendasi sementara: 4 produk terbaru yang stoknya tersedia (semua dari tabel produk)
            'produk' => DB::table('produk as p')
                ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
                ->where('p.stok', '>', 0)->orderByDesc('p.id_produk')->limit(4)
                ->get(['p.*', 'k.nama_kategori']),
        ]);
    }
}
