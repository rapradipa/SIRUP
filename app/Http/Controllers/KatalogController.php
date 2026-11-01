<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KatalogController extends Controller
{
    private const URUTAN = ['terbaru' => 'Terbaru', 'termurah' => 'Harga termurah', 'termahal' => 'Harga termahal', 'nama' => 'Nama A-Z'];

    public function index(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 100);
        $kat = array_values(array_filter(array_map('intval', (array) $r->query('kategori', []))));
        $min = is_numeric($r->query('min')) ? max(0, (float) $r->query('min')) : null;
        $maks = is_numeric($r->query('maks')) ? max(0, (float) $r->query('maks')) : null;
        $ada = $r->boolean('tersedia');
        $urut = array_key_exists($r->query('urut'), self::URUTAN) ? $r->query('urut') : 'terbaru';
        $akhir = 'p.harga * (100 - p.diskon) / 100'; // harga setelah diskon

        $query = DB::table('produk as p')
            ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
            ->when($q !== '', fn ($b) => $b->where('p.nama_produk', 'like', '%' . addcslashes($q, '%_\\') . '%'))
            ->when($kat, fn ($b) => $b->whereIn('p.id_kategori', $kat))
            ->when($min !== null, fn ($b) => $b->whereRaw("$akhir >= ?", [$min]))
            ->when($maks !== null, fn ($b) => $b->whereRaw("$akhir <= ?", [$maks]))
            ->when($ada, fn ($b) => $b->where('p.stok', '>', 0));

        match ($urut) {
            'termurah' => $query->orderByRaw("$akhir asc"),
            'termahal' => $query->orderByRaw("$akhir desc"),
            'nama' => $query->orderBy('p.nama_produk'),
            default => $query->orderByDesc('p.id_produk'),
        };

        $kategori = DB::table('kategori')->orderBy('id_kategori')->get();
        $judul = count($kat) === 1 ? ($kategori->firstWhere('id_kategori', $kat[0])->nama_kategori ?? 'Katalog')
            : ($q !== '' ? 'Hasil untuk "' . $q . '"' : 'Semua Produk');

        return view('pelanggan.katalog', [
            'produk' => $query->paginate(9, ['p.*', 'k.nama_kategori'])->withQueryString(),
            'kategori' => $kategori, 'judul' => $judul, 'urutan' => self::URUTAN,
            'f' => compact('q', 'kat', 'min', 'maks', 'ada', 'urut'),
        ]);
    }

    public function show(int $id)
    {
        $p = DB::table('produk as p')
            ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
            ->leftJoin('supplier as s', 's.id_supplier', '=', 'p.id_supplier')
            ->where('p.id_produk', $id)->first(['p.*', 'k.nama_kategori', 's.nama_supplier']);
        abort_if(! $p, 404);
        return view('pelanggan.produk', ['p' => $p]);
    }
}
