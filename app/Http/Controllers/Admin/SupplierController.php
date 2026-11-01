<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    private const ATURAN = [
        'nama_supplier' => 'required|string|max:100',
        'alamat' => 'nullable|string|max:255',
        'no_telpon' => ['nullable', 'regex:/^[0-9+]{8,15}$/'],
    ];

    public function index()
    {
        return view('admin.supplier', ['supplier' => DB::table('supplier')->orderBy('nama_supplier')->paginate(10)]);
    }

    public function baru()
    {
        return view('admin.supplier_form', ['s' => null]);
    }

    public function ubah(int $id)
    {
        $s = DB::table('supplier')->where('id_supplier', $id)->first();
        abort_if(! $s, 404);
        return view('admin.supplier_form', compact('s'));
    }

    public function simpan(Request $r)
    {
        $d = $r->validate(self::ATURAN, Pesan::VALIDASI);
        DB::table('supplier')->insert($d + ['alamat' => null, 'no_telpon' => null]);
        return redirect()->route('admin.supplier')->with('ok', "Supplier \"{$d['nama_supplier']}\" ditambahkan.");
    }

    public function perbarui(Request $r, int $id)
    {
        abort_if(! DB::table('supplier')->where('id_supplier', $id)->exists(), 404);
        $d = $r->validate(self::ATURAN, Pesan::VALIDASI);
        DB::table('supplier')->where('id_supplier', $id)->update($d + ['alamat' => null, 'no_telpon' => null]);
        return redirect()->route('admin.supplier')->with('ok', "Supplier \"{$d['nama_supplier']}\" diperbarui.");
    }

    public function hapus(int $id)
    {
        $s = DB::table('supplier')->where('id_supplier', $id)->first();
        abort_if(! $s, 404);
        if (DB::table('produk')->where('id_supplier', $id)->exists() || DB::table('stok_masuk')->where('id_supplier', $id)->exists()) {
            return back()->with('err', "Supplier \"{$s->nama_supplier}\" masih dipakai produk atau riwayat stok masuk, jadi tidak bisa dihapus.");
        }
        DB::table('supplier')->where('id_supplier', $id)->delete();
        return back()->with('ok', "Supplier \"{$s->nama_supplier}\" dihapus.");
    }
}
