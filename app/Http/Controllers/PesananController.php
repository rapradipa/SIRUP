<?php

namespace App\Http\Controllers;

use App\Services\PengirimanService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PesananController extends Controller
{
    private const FILTER = ['semua' => 'Semua', 'menunggu' => 'Menunggu Pembayaran', 'lunas' => 'Lunas', 'dikirim' => 'Dikirim', 'selesai' => 'Selesai'];
    private const BAYAR = "LOWER(COALESCE(b.status_pembayaran,''))";
    private const KIRIM = "LOWER(COALESCE(g.status_pengiriman,''))";

    private function dasar()
    {
        return DB::table('transaksi as t')
            ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->leftJoin('pengiriman as g', 'g.id_transaksi', '=', 't.id_transaksi')
            ->where('t.id_pelanggan', auth()->user()->id_pelanggan);
    }

    private function idDariKode(string $q): ?int
    {
        return preg_match('/^(?:TRX-?)?0*(\d+)$/i', $q, $m) ? (int) $m[1] : null;
    }

    public function index(Request $r)
    {
        $status = array_key_exists($r->query('status'), self::FILTER) ? $r->query('status') : 'semua';
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 100);

        $query = $this->dasar();
        match ($status) {
            'menunggu' => $query->whereRaw(self::BAYAR . " <> 'lunas'"),
            'lunas' => $query->whereRaw(self::BAYAR . " = 'lunas'"),
            'dikirim' => $query->whereRaw(self::KIRIM . " = 'dikirim'"),
            'selesai' => $query->whereRaw(self::KIRIM . " = 'selesai'"),
            default => null,
        };
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->whereExists(fn ($s) => $s->selectRaw('1')->from('detail_transaksi as d')
                    ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')->whereColumn('d.id_transaksi', 't.id_transaksi')
                    ->where('p.nama_produk', 'like', '%' . addcslashes($q, '%_\\') . '%'));
                if (($kode = $this->idDariKode($q)) !== null) $w->orWhere('t.id_transaksi', $kode);
            });
        }

        $pesanan = $query->orderByDesc('t.id_transaksi')
            ->paginate(8, ['t.id_transaksi', 't.tanggal', 't.total', 'b.status_pembayaran', 'g.status_pengiriman', 'g.jenis'])->withQueryString();
        $item = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->whereIn('d.id_transaksi', $pesanan->pluck('id_transaksi'))->orderBy('d.id_detail')
            ->get(['d.id_transaksi', 'p.nama_produk'])->groupBy('id_transaksi');

        $ringkas = [
            'total' => $this->dasar()->count(),
            'menunggu' => $this->dasar()->whereRaw(self::BAYAR . " <> 'lunas'")->count(),
            'dikirim' => $this->dasar()->whereRaw(self::KIRIM . " = 'dikirim'")->count(),
        ];
        return view('pelanggan.pesanan', ['pesanan' => $pesanan, 'item' => $item, 'ringkas' => $ringkas,
            'filter' => self::FILTER, 'status' => $status, 'q' => $q]);
    }

    public function show(int $id)
    {
        $t = $this->dasar()->where('t.id_transaksi', $id)->first(['t.*', 'b.status_pembayaran', 'b.metode', 'b.jumlah as dibayar', 'b.dibayar_pada',
            'g.status_pengiriman', 'g.jenis', 'g.alamat_pengiriman', 'g.nama_penerima', 'g.no_telp_penerima', 'g.no_resi']);
        abort_if(! $t, 404);
        $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('d.id_transaksi', $id)->get(['d.jumlah', 'd.subtotal', 'p.nama_produk', 'p.id_kategori', 'p.gambar', 'p.harga', 'p.diskon']);
        return view('pelanggan.pesanan_detail', compact('t', 'items'));
    }

    public function invoice(int $id)
    {
        $t = $this->dasar()->where('t.id_transaksi', $id)->first(['t.*', 'b.status_pembayaran', 'b.metode', 'b.dibayar_pada',
            'g.jenis', 'g.alamat_pengiriman', 'g.nama_penerima', 'g.no_telp_penerima', 'g.kurir', 'g.no_resi']);
        abort_if(! $t, 404);
        $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->leftJoin('kategori as k', 'k.id_kategori', '=', 'p.id_kategori')
            ->where('d.id_transaksi', $id)->orderBy('d.id_detail')
            ->get(['d.jumlah', 'd.subtotal', 'p.nama_produk', 'p.harga', 'p.diskon', 'k.nama_kategori']);
        return view('pelanggan.invoice', compact('t', 'items'));
    }

    public function lacak(Request $r, ?int $id = null)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 60);
        $dariRute = $id !== null;
        if (! $dariRute && $q !== '') {
            $id = $this->idDariKode($q) ?? DB::table('pengiriman as g')->join('transaksi as t', 't.id_transaksi', '=', 'g.id_transaksi')
                ->where('t.id_pelanggan', auth()->user()->id_pelanggan)->where('g.no_resi', $q)->value('g.id_transaksi');
        }

        $t = $id ? $this->dasar()->where('t.id_transaksi', $id)->first(['t.*', 'b.status_pembayaran', 'b.dibayar_pada']) : null;
        abort_if($dariRute && ! $t, 404);

        $data = ['q' => $q, 't' => $t, 'dicari' => $q !== '' || $dariRute];
        if ($t) {
            $g = DB::table('pengiriman')->where('id_transaksi', $t->id_transaksi)->first();
            $log = DB::table('pengiriman_log')->where('id_transaksi', $t->id_transaksi)->orderBy('id_log')->get();
            $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
                ->where('d.id_transaksi', $t->id_transaksi)->get(['d.jumlah', 'p.nama_produk', 'p.id_kategori', 'p.gambar']);
            [$langkah, $riwayat] = $this->susun($t, $g, $log);
            $data += compact('g', 'items', 'langkah', 'riwayat');
        }
        return view('pelanggan.lacak', $data);
    }

    public function terima(int $id, PengirimanService $layanan)
    {
        $g = $this->dasar()->where('t.id_transaksi', $id)->first(['g.status_pengiriman']);
        abort_if(! $g, 404);
        if (strtolower($g->status_pengiriman ?? '') !== 'dikirim') return back()->with('err', 'Pesanan ini belum berstatus dikirim.');
        try {
            $layanan->ubah($id, 'selesai', ['catatan' => 'Pesanan dikonfirmasi diterima oleh pelanggan']);
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }
        return back()->with('ok', 'Terima kasih! Pesanan ditandai sudah diterima.');
    }

    /** Susun stepper dan riwayat perjalanan (terbaru di atas) dari data transaksi, pembayaran, dan log. */
    private function susun($t, $g, $log): array
    {
        $kode = 'TRX-' . str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT);
        $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas';
        $s = strtolower($g->status_pengiriman ?? '');
        $ambil = ($g->jenis ?? '') === 'ambil';

        $acara = [['Pesanan dibuat', "Transaksi $kode tercatat di sistem", Carbon::parse($t->tanggal), false]];
        if ($lunas) $acara[] = ['Pembayaran lunas', "Pembayaran transaksi $kode terverifikasi", $t->dibayar_pada ? Carbon::parse($t->dibayar_pada) : null, true];
        $judul = ['diproses' => 'Pesanan diproses', 'dikirim' => 'Pesanan diserahkan ke kurir', 'selesai' => $ambil ? 'Pesanan diambil' : 'Pesanan diterima'];
        foreach ($log as $l) $acara[] = [$judul[$l->status] ?? ucfirst($l->status), $l->keterangan, Carbon::parse($l->dibuat_pada), true];

        $langkah = [['Dibuat', true], ['Lunas', $lunas], ['Diproses', $g !== null]];
        $langkah[] = $ambil ? ['Diambil', $s === 'selesai'] : ['Dikirim', in_array($s, ['dikirim', 'selesai'], true)];
        if (! $ambil) $langkah[] = ['Diterima', $s === 'selesai'];

        return [$langkah, array_reverse($acara)];
    }
}
