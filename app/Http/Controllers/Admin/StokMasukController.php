<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokMasukController extends Controller
{
    public function index()
    {
        return view('admin.stok', [
            'supplier' => DB::table('supplier')->orderBy('nama_supplier')->get(),
            'produk' => DB::table('produk as p')->leftJoin('supplier as s', 's.id_supplier', '=', 'p.id_supplier')
                ->orderBy('p.nama_produk')->get(['p.id_produk', 'p.nama_produk', 'p.stok', 's.nama_supplier']),
            'riwayat' => DB::table('stok_masuk as m')->join('supplier as s', 's.id_supplier', '=', 'm.id_supplier')
                ->join('produk as p', 'p.id_produk', '=', 'm.id_produk')->orderByDesc('m.id_stok_masuk')
                ->paginate(10, ['m.id_stok_masuk', 'm.tanggal', 'm.jumlah', 's.nama_supplier', 'p.nama_produk']),
        ]);
    }

    public function simpan(Request $r)
    {
        $d = $r->validate([
            'id_supplier' => 'required|integer|exists:supplier,id_supplier',
            'id_produk' => 'required|integer|exists:produk,id_produk',
            'jumlah' => 'required|integer|min:1|max:100000',
            'tanggal' => 'required|date|before_or_equal:today',
        ], Pesan::VALIDASI);

        $p = null;
        DB::transaction(function () use ($d, &$p) {
            $p = DB::table('produk')->where('id_produk', $d['id_produk'])->lockForUpdate()->first();
            DB::table('stok_masuk')->insert($d);
            DB::table('produk')->where('id_produk', $d['id_produk'])->increment('stok', $d['jumlah']);
        });
        return redirect()->route('admin.stok')->with('ok', "Stok {$p->nama_produk} bertambah {$d['jumlah']} (sekarang " . ($p->stok + $d['jumlah']) . ').');
    }
}
