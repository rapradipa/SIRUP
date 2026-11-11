<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KeranjangController extends Controller
{
    private function pid(): int
    {
        return (int) auth()->user()->id_pelanggan;
    }

    /** Jumlah total barang di keranjang (sama dengan angka di header). */
    private function jumlahKeranjang(): int
    {
        return (int) DB::table('keranjang')->where('id_pelanggan', $this->pid())->sum('jumlah');
    }

    /** Balasan JSON untuk permintaan AJAX (tanpa reload halaman). */
    private function balas(bool $ok, string $pesan = '', int $status = 200)
    {
        return response()->json([
            'ok' => $ok,
            'pesan' => $pesan,
            'jumlah_keranjang' => $this->jumlahKeranjang(),
        ], $status);
    }

    private function item(int $id)
    {
        return DB::table('keranjang as c')->join('produk as p', 'p.id_produk', '=', 'c.id_produk')
            ->where('c.id_keranjang', $id)->where('c.id_pelanggan', $this->pid())
            ->first(['c.id_keranjang', 'c.jumlah', 'p.stok']);
    }

    public function index()
    {
        $items = DB::table('keranjang as c')->join('produk as p', 'p.id_produk', '=', 'c.id_produk')
            ->where('c.id_pelanggan', $this->pid())->orderBy('c.id_keranjang')
            ->get(['c.id_keranjang', 'c.jumlah', 'p.id_produk', 'p.nama_produk', 'p.harga', 'p.diskon', 'p.stok', 'p.id_kategori', 'p.gambar']);
        return view('pelanggan.keranjang', compact('items'));
    }

    public function tambah(Request $r)
    {
        $d = $r->validate([
            'id_produk' => 'required|integer|exists:produk,id_produk',
            'jumlah' => 'nullable|integer|min:1|max:999',
            'aksi' => 'nullable|in:tambah,beli',
        ]);
        $p = DB::table('produk')->where('id_produk', $d['id_produk'])->first();
        if ($p->stok <= 0) {
            $pesan = "Maaf, stok {$p->nama_produk} sedang habis.";
            return $r->expectsJson() ? $this->balas(false, $pesan) : back()->with('err', $pesan);
        }

        $jml = $d['jumlah'] ?? 1; $pid = $this->pid(); $batas = false;
        DB::transaction(function () use ($p, $jml, $pid, &$batas) {
            $ada = DB::table('keranjang')->where('id_pelanggan', $pid)->where('id_produk', $p->id_produk)->lockForUpdate()->first();
            $baru = ($ada->jumlah ?? 0) + $jml;
            if ($baru > $p->stok) { $baru = $p->stok; $batas = true; }
            if ($ada) DB::table('keranjang')->where('id_keranjang', $ada->id_keranjang)->update(['jumlah' => $baru]);
            else DB::table('keranjang')->insert(['id_pelanggan' => $pid, 'id_produk' => $p->id_produk, 'jumlah' => $baru]);
        });

        if (($d['aksi'] ?? '') === 'beli') return redirect()->route('keranjang');
        $pesan = $batas ? "Jumlah dibatasi sesuai stok {$p->nama_produk} ({$p->stok})." : "{$p->nama_produk} ditambahkan ke keranjang.";
        if ($r->expectsJson()) return $this->balas(! $batas, $pesan);
        return back()->with($batas ? 'err' : 'ok', $pesan);
    }

    public function ubah(Request $r, int $id)
    {
        $i = $this->item($id);
        abort_if(! $i, 404);
        $kurang = $r->input('aksi') === 'kurang';
        $baru = max(1, min($i->jumlah + ($kurang ? -1 : 1), max($i->stok, 1)));
        DB::table('keranjang')->where('id_keranjang', $id)->update(['jumlah' => $baru]);
        $penuh = ! $kurang && $baru === $i->jumlah;
        if ($r->expectsJson()) return $this->balas(! $penuh, $penuh ? 'Jumlah sudah mencapai stok yang tersedia.' : '');
        return $penuh ? back()->with('err', 'Jumlah sudah mencapai stok yang tersedia.') : back();
    }

    public function buang(Request $r, int $id)
    {
        DB::table('keranjang')->where('id_keranjang', $id)->where('id_pelanggan', $this->pid())->delete();
        if ($r->expectsJson()) return $this->balas(true);
        return back();
    }

    public function hapus(Request $r)
    {
        $ids = array_map('intval', (array) $r->input('ids', []));
        if (! $ids) {
            return $r->expectsJson() ? $this->balas(false, 'Pilih barang yang ingin dihapus.') : back()->with('err', 'Pilih barang yang ingin dihapus.');
        }
        DB::table('keranjang')->where('id_pelanggan', $this->pid())->whereIn('id_keranjang', $ids)->delete();
        if ($r->expectsJson()) return $this->balas(true, 'Barang terpilih dihapus dari keranjang.');
        return back()->with('ok', 'Barang terpilih dihapus dari keranjang.');
    }

    public function checkout(Request $r)
    {
        $ids = array_map('intval', (array) $r->input('ids', []));
        if (! $ids) return back()->with('err', 'Pilih minimal satu barang untuk dibayar.');
        $pid = $this->pid();

        try {
            $idTrx = DB::transaction(function () use ($ids, $pid) {
                $items = DB::table('keranjang as c')->join('produk as p', 'p.id_produk', '=', 'c.id_produk')
                    ->where('c.id_pelanggan', $pid)->whereIn('c.id_keranjang', $ids)
                    ->get(['c.id_keranjang', 'c.jumlah', 'p.id_produk', 'p.nama_produk', 'p.harga', 'p.diskon', 'p.stok']);
                if ($items->isEmpty()) throw new RuntimeException('Barang tidak ditemukan di keranjang.');

                $total = 0; $detail = [];
                foreach ($items as $i) {
                    if ($i->stok < $i->jumlah) throw new RuntimeException("Stok {$i->nama_produk} tidak mencukupi (sisa {$i->stok}).");
                    $sub = round($i->harga * (100 - $i->diskon) / 100 * $i->jumlah, 2);
                    $total += $sub;
                    $detail[] = ['id_produk' => $i->id_produk, 'jumlah' => $i->jumlah, 'subtotal' => $sub];
                }
                $id = DB::table('transaksi')->insertGetId(['tanggal' => today()->toDateString(), 'total' => $total, 'id_pelanggan' => $pid]);
                DB::table('detail_transaksi')->insert(array_map(fn ($d) => $d + ['id_transaksi' => $id], $detail));
                DB::table('pembayaran')->insert(['id_transaksi' => $id, 'jumlah' => $total, 'status_pembayaran' => 'menunggu']);
                DB::table('keranjang')->where('id_pelanggan', $pid)->whereIn('id_keranjang', $items->pluck('id_keranjang'))->delete();
                return $id;
            });
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }
        return redirect()->route('pembayaran', $idTrx);
    }
}
