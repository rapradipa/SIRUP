<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = DB::table('kategori as k')->leftJoin('produk as p', 'p.id_kategori', '=', 'k.id_kategori')
            ->groupBy('k.id_kategori', 'k.nama_kategori')->orderBy('k.nama_kategori')
            ->get(['k.id_kategori', 'k.nama_kategori', DB::raw('COUNT(p.id_produk) as jumlah_produk')]);
        return view('admin.kategori', compact('kategori'));
    }

    public function simpan(Request $r)
    {
        $d = $r->validate(['nama_kategori' => 'required|string|max:100'], Pesan::VALIDASI);
        DB::table('kategori')->insert(['nama_kategori' => trim($d['nama_kategori'])]);
        return back()->with('ok', 'Kategori ditambahkan.');
    }

    public function perbarui(Request $r, int $id)
    {
        abort_if(! DB::table('kategori')->where('id_kategori', $id)->exists(), 404);
        $d = $r->validate(['nama_kategori' => 'required|string|max:100'], Pesan::VALIDASI);
        DB::table('kategori')->where('id_kategori', $id)->update(['nama_kategori' => trim($d['nama_kategori'])]);
        return back()->with('ok', 'Kategori diperbarui.');
    }

    public function hapus(int $id)
    {
        $k = DB::table('kategori')->where('id_kategori', $id)->first();
        abort_if(! $k, 404);
        if (DB::table('produk')->where('id_kategori', $id)->exists()) {
            return back()->with('err', "Kategori \"{$k->nama_kategori}\" masih dipakai produk, jadi tidak bisa dihapus.");
        }
        DB::table('kategori')->where('id_kategori', $id)->delete();
        return back()->with('ok', "Kategori \"{$k->nama_kategori}\" dihapus.");
    }
}
