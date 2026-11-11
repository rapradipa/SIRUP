<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Pencarian global panel admin (kolom "Cari data..." di bilah atas).
 * Admin mencari di semua data; kasir hanya di transaksi & pengiriman
 * (sesuai menu yang boleh ia buka).
 */
class CariController extends Controller
{
    private const BATAS = 8;

    private function rp($n): string
    {
        return 'Rp ' . number_format((float) $n, 0, ',', '.');
    }

    private function kode($id): string
    {
        return 'TRX-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    /** Ambil total + beberapa baris pertama dari satu query. */
    private function ambil($builder, array $kolom): array
    {
        $total = (clone $builder)->count();
        return [$total, $total ? $builder->limit(self::BATAS)->get($kolom) : collect()];
    }

    public function index(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 100);
        $admin = $r->user()->role === 'admin';
        $bagian = [];

        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            // "TRX-0007", "trx7", atau "7" => cari juga berdasarkan nomor transaksi
            $idTrx = preg_match('/^(?:trx-?)?0*(\d+)$/i', $q, $m) ? (int) $m[1] : null;

            // ---------- Transaksi (admin & kasir) ----------
            [$total, $rows] = $this->ambil(
                DB::table('transaksi as t')
                    ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
                    ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
                    ->leftJoin('pengiriman as g', 'g.id_transaksi', '=', 't.id_transaksi')
                    ->where(function ($w) use ($like, $idTrx) {
                        $w->where('p.nama', 'like', $like)
                          ->orWhere('p.no_telp', 'like', $like)
                          ->orWhere('b.status_pembayaran', 'like', $like);
                        if ($idTrx) $w->orWhere('t.id_transaksi', $idTrx);
                    })
                    ->orderByDesc('t.id_transaksi'),
                ['t.id_transaksi', 't.tanggal', 't.total', 'p.nama', 'p.no_telp', 'b.status_pembayaran', 'g.id_pengiriman']
            );
            $bagian[] = [
                'judul' => 'Transaksi', 'total' => $total, 'semua' => route('admin.transaksi'),
                'head' => ['No. Transaksi', 'Tanggal', 'Pelanggan', 'Total', 'Pembayaran'],
                'rows' => $rows->map(function ($t) {
                    $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas';
                    return [
                        ['t' => $this->kode($t->id_transaksi), 'href' => $t->id_pengiriman ? route('admin.pengiriman.show', $t->id_transaksi) : route('admin.transaksi')],
                        ['t' => Carbon::parse($t->tanggal)->format('d/m/Y')],
                        ['t' => $t->nama . ($t->no_telp ? ' (' . $t->no_telp . ')' : '')],
                        ['t' => $this->rp($t->total)],
                        ['t' => $lunas ? 'Lunas' : 'Menunggu', 'bd' => $lunas ? 'g' : 'y'],
                    ];
                })->all(),
            ];

            // ---------- Pengiriman (admin & kasir) ----------
            [$total, $rows] = $this->ambil(
                DB::table('pengiriman as g')
                    ->join('transaksi as t', 't.id_transaksi', '=', 'g.id_transaksi')
                    ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
                    ->where(function ($w) use ($like, $idTrx) {
                        $w->where('g.no_resi', 'like', $like)
                          ->orWhere('g.nama_penerima', 'like', $like)
                          ->orWhere('g.alamat_pengiriman', 'like', $like)
                          ->orWhere('g.kota', 'like', $like)
                          ->orWhere('g.kurir', 'like', $like)
                          ->orWhere('g.status_pengiriman', 'like', $like)
                          ->orWhere('p.nama', 'like', $like);
                        if ($idTrx) $w->orWhere('g.id_transaksi', $idTrx);
                    })
                    ->orderByDesc('g.id_transaksi'),
                ['g.id_transaksi', 'g.nama_penerima', 'g.status_pengiriman', 'g.kurir', 'g.no_resi', 'p.nama']
            );
            $bagian[] = [
                'judul' => 'Pengiriman', 'total' => $total, 'semua' => route('admin.pengiriman'),
                'head' => ['No. Transaksi', 'Penerima', 'Kurir / Resi', 'Status'],
                'rows' => $rows->map(fn ($g) => [
                    ['t' => $this->kode($g->id_transaksi), 'href' => route('admin.pengiriman.show', $g->id_transaksi)],
                    ['t' => $g->nama_penerima ?: $g->nama],
                    ['t' => trim(($g->kurir ?: '-') . ($g->no_resi ? ' / ' . $g->no_resi : ''))],
                    ['t' => ucfirst($g->status_pengiriman ?: '-'), 'bd' => 'b'],
                ])->all(),
            ];

            if ($admin) {
                // ---------- Produk ----------
                [$total, $rows] = $this->ambil(
                    DB::table('produk as p')
                        ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
                        ->leftJoin('supplier as s', 's.id_supplier', '=', 'p.id_supplier')
                        ->where(fn ($w) => $w->where('p.nama_produk', 'like', $like)
                            ->orWhere('k.nama_kategori', 'like', $like)
                            ->orWhere('s.nama_supplier', 'like', $like))
                        ->orderBy('p.nama_produk'),
                    ['p.id_produk', 'p.nama_produk', 'p.harga', 'p.stok', 'k.nama_kategori']
                );
                $bagian[] = [
                    'judul' => 'Produk', 'total' => $total, 'semua' => route('admin.produk', ['q' => $q]),
                    'head' => ['Produk', 'Kategori', 'Harga', 'Stok'],
                    'rows' => $rows->map(function ($p) {
                        [$kls, $lbl] = Stok::status((int) $p->stok);
                        return [
                            ['t' => $p->nama_produk, 'href' => route('admin.produk.ubah', $p->id_produk)],
                            ['t' => $p->nama_kategori ?: '-'],
                            ['t' => $this->rp($p->harga)],
                            ['t' => $p->stok . ' (' . $lbl . ')', 'bd' => $kls],
                        ];
                    })->all(),
                ];

                // ---------- Kategori ----------
                [$total, $rows] = $this->ambil(
                    DB::table('kategori')->where('nama_kategori', 'like', $like)->orderBy('nama_kategori'),
                    ['id_kategori', 'nama_kategori']
                );
                $bagian[] = [
                    'judul' => 'Kategori', 'total' => $total, 'semua' => route('admin.kategori'),
                    'head' => ['Nama Kategori'],
                    'rows' => $rows->map(fn ($k) => [
                        ['t' => $k->nama_kategori, 'href' => route('admin.produk', ['kategori' => $k->id_kategori])],
                    ])->all(),
                ];

                // ---------- Supplier ----------
                [$total, $rows] = $this->ambil(
                    DB::table('supplier')->where(fn ($w) => $w->where('nama_supplier', 'like', $like)
                        ->orWhere('alamat', 'like', $like)->orWhere('no_telpon', 'like', $like))
                        ->orderBy('nama_supplier'),
                    ['id_supplier', 'nama_supplier', 'alamat', 'no_telpon']
                );
                $bagian[] = [
                    'judul' => 'Supplier', 'total' => $total, 'semua' => route('admin.supplier'),
                    'head' => ['Supplier', 'Alamat', 'No. Telepon'],
                    'rows' => $rows->map(fn ($s) => [
                        ['t' => $s->nama_supplier, 'href' => route('admin.supplier.ubah', $s->id_supplier)],
                        ['t' => $s->alamat ?: '-'],
                        ['t' => $s->no_telpon ?: '-'],
                    ])->all(),
                ];

                // ---------- Pengguna ----------
                [$total, $rows] = $this->ambil(
                    DB::table('akun as a')
                        ->leftJoin('karyawan as k', 'k.id_karyawan', '=', 'a.id_karyawan')
                        ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'a.id_pelanggan')
                        ->where(fn ($w) => $w->whereRaw('COALESCE(k.nama, p.nama) like ?', [$like])
                            ->orWhere('a.no_telp', 'like', $like))
                        ->orderBy('a.id_akun'),
                    ['a.id_akun', 'a.no_telp', 'a.role', DB::raw('COALESCE(k.nama, p.nama) as nama')]
                );
                $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g'];
                $bagian[] = [
                    'judul' => 'Pengguna', 'total' => $total, 'semua' => route('admin.pengguna'),
                    'head' => ['Nama', 'No. Telepon', 'Peran'],
                    'rows' => $rows->map(fn ($a) => [
                        ['t' => $a->nama ?? '-', 'href' => route('admin.pengguna.show', $a->id_akun)],
                        ['t' => $a->no_telp],
                        ['t' => ucfirst($a->role), 'bd' => $kls[$a->role] ?? 'b'],
                    ])->all(),
                ];
            }
        }

        return view('admin.cari', [
            'q' => $q,
            'bagian' => $bagian,
            'jumlah' => collect($bagian)->sum('total'),
            'batas' => self::BATAS,
        ]);
    }
}
