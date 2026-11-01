<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    private const FOLDER = 'uploads/produk';

    public function index(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 100);
        $kat = (int) $r->query('kategori', 0);
        $produk = DB::table('produk as p')
            ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
            ->leftJoin('supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->when($q !== '', fn ($b) => $b->where('p.nama_produk', 'like', '%' . addcslashes($q, '%_\\') . '%'))
            ->when($kat > 0, fn ($b) => $b->where('p.id_kategori', $kat))
            ->orderBy('p.nama_produk')
            ->paginate(10, ['p.*', 'k.nama_kategori', 's.nama_supplier'])->withQueryString();
        return view('admin.produk', ['produk' => $produk, 'q' => $q, 'kat' => $kat,
            'kategori' => DB::table('kategori')->orderBy('nama_kategori')->get()]);
    }

    private function opsi(): array
    {
        return ['kategori' => DB::table('kategori')->orderBy('nama_kategori')->get(), 'supplier' => DB::table('supplier')->orderBy('nama_supplier')->get()];
    }

    public function baru()
    {
        return view('admin.produk_form', ['p' => null] + $this->opsi());
    }

    public function ubah(int $id)
    {
        $p = DB::table('produk')->where('id_produk', $id)->first();
        abort_if(! $p, 404);
        return view('admin.produk_form', ['p' => $p] + $this->opsi());
    }

    private function aturan(bool $baru): array
    {
        return [
            'nama_produk' => 'required|string|max:100',
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'id_supplier' => 'required|integer|exists:supplier,id_supplier',
            'harga' => 'required|numeric|min:0|max:99999999',
            'diskon' => 'nullable|integer|min:0|max:90',
            'deskripsi' => 'nullable|string|max:2000',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ] + ($baru ? ['stok' => 'required|integer|min:0|max:100000'] : []);
    }

    private function simpanFoto(Request $r): ?string
    {
        if (! $r->hasFile('foto')) return null;
        File::ensureDirectoryExists(public_path(self::FOLDER));
        $nama = Str::random(24) . '.' . $r->file('foto')->extension();
        $r->file('foto')->move(public_path(self::FOLDER), $nama);
        return $nama;
    }

    private function hapusFoto(?string $nama): void
    {
        if ($nama) File::delete(public_path(self::FOLDER . '/' . basename($nama)));
    }

    public function simpan(Request $r)
    {
        $d = $r->validate($this->aturan(true), Pesan::VALIDASI);
        DB::table('produk')->insert([
            'nama_produk' => $d['nama_produk'], 'id_kategori' => $d['id_kategori'], 'id_supplier' => $d['id_supplier'],
            'harga' => $d['harga'], 'stok' => $d['stok'], 'diskon' => (int) ($d['diskon'] ?? 0),
            'deskripsi' => $d['deskripsi'] ?? null, 'gambar' => $this->simpanFoto($r),
        ]);
        return redirect()->route('admin.produk')->with('ok', "Produk \"{$d['nama_produk']}\" ditambahkan.");
    }

    public function perbarui(Request $r, int $id)
    {
        $p = DB::table('produk')->where('id_produk', $id)->first();
        abort_if(! $p, 404);
        $d = $r->validate($this->aturan(false), Pesan::VALIDASI);

        $gambar = $p->gambar;
        if ($baru = $this->simpanFoto($r)) { $this->hapusFoto($p->gambar); $gambar = $baru; }
        elseif ($r->boolean('hapus_foto')) { $this->hapusFoto($p->gambar); $gambar = null; }

        // Stok sengaja tidak diubah di sini; perubahan stok lewat menu Stok Masuk (jejak audit).
        DB::table('produk')->where('id_produk', $id)->update([
            'nama_produk' => $d['nama_produk'], 'id_kategori' => $d['id_kategori'], 'id_supplier' => $d['id_supplier'],
            'harga' => $d['harga'], 'diskon' => (int) ($d['diskon'] ?? 0), 'deskripsi' => $d['deskripsi'] ?? null, 'gambar' => $gambar,
        ]);
        return redirect()->route('admin.produk')->with('ok', "Produk \"{$d['nama_produk']}\" diperbarui.");
    }

    public function hapus(int $id)
    {
        $p = DB::table('produk')->where('id_produk', $id)->first();
        abort_if(! $p, 404);
        if (DB::table('detail_transaksi')->where('id_produk', $id)->exists() || DB::table('stok_masuk')->where('id_produk', $id)->exists()) {
            return back()->with('err', "Produk \"{$p->nama_produk}\" sudah punya riwayat transaksi atau stok masuk, jadi tidak bisa dihapus.");
        }
        DB::transaction(function () use ($id) {
            DB::table('keranjang')->where('id_produk', $id)->delete();
            DB::table('produk')->where('id_produk', $id)->delete();
        });
        $this->hapusFoto($p->gambar);
        return back()->with('ok', "Produk \"{$p->nama_produk}\" dihapus.");
    }
}
