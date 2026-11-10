<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\IkonKategori;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KategoriController extends Controller
{
    public function index()
    {
        $kategori = DB::table('kategori as k')->leftJoin('produk as p', 'p.id_kategori', '=', 'k.id_kategori')
            ->groupBy('k.id_kategori', 'k.nama_kategori', 'k.ikon')->orderBy('k.nama_kategori')
            ->get(['k.id_kategori', 'k.nama_kategori', 'k.ikon', DB::raw('COUNT(p.id_produk) as jumlah_produk')]);
        $pilihan = IkonKategori::PILIHAN;
        return view('admin.kategori', compact('kategori', 'pilihan'));
    }

    private function validasi(Request $r): array
    {
        return $r->validate([
            'nama_kategori' => 'required|string|max:100',
            'ikon' => ['nullable', Rule::in(array_keys(IkonKategori::PILIHAN))],
        ], array_merge(Pesan::VALIDASI, ['ikon.in' => 'Ikon yang dipilih tidak valid.']));
    }

    public function simpan(Request $r)
    {
        $d = $this->validasi($r);
        DB::table('kategori')->insert([
            'nama_kategori' => trim($d['nama_kategori']),
            'ikon' => $d['ikon'] ?? 'tag',
        ]);
        return back()->with('ok', 'Kategori ditambahkan.');
    }

    public function perbarui(Request $r, int $id)
    {
        abort_if(! DB::table('kategori')->where('id_kategori', $id)->exists(), 404);
        $d = $this->validasi($r);
        $data = ['nama_kategori' => trim($d['nama_kategori'])];
        if (! empty($d['ikon'])) $data['ikon'] = $d['ikon'];
        DB::table('kategori')->where('id_kategori', $id)->update($data);
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
