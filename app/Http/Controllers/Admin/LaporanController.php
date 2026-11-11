<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    private const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    private const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    private const LUNAS = "LOWER(COALESCE(b.status_pembayaran,'')) = 'lunas'";
    private const KOLOM = ['t.id_transaksi', 't.tanggal', 't.total', 'p.nama', 'b.status_pembayaran', 'g.status_pengiriman'];

    /** Periode dari tab (harian/mingguan/bulanan) atau tanggal kustom; maksimal 366 hari. */
    private function periode(Request $r): array
    {
        $mode = in_array($r->query('mode'), ['harian', 'mingguan', 'bulanan'], true) ? $r->query('mode') : 'harian';
        $hari = today();
        [$a, $b] = match ($mode) {
            'mingguan' => [$hari->copy()->startOfWeek(), $hari->copy()->endOfWeek()->startOfDay()],
            'bulanan' => [$hari->copy()->startOfMonth(), $hari->copy()->endOfMonth()->startOfDay()],
            default => [$hari->copy(), $hari->copy()],
        };
        $tgl = function ($v, $bawaan) {
            if (! is_string($v) || ! Carbon::hasFormat($v, 'Y-m-d')) return $bawaan;
            try { return Carbon::createFromFormat('Y-m-d', $v)->startOfDay(); } catch (\Throwable) { return $bawaan; }
        };
        $dari = $tgl($r->query('dari'), $a); $sampai = $tgl($r->query('sampai'), $b);
        if ($sampai->lt($dari)) [$dari, $sampai] = [$sampai, $dari];
        if ((int) $dari->diffInDays($sampai) > 366) $sampai = $dari->copy()->addDays(366);
        return [$mode, $dari, $sampai];
    }

    private function dasar(Carbon $dari, Carbon $sampai)
    {
        return DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->leftJoin('pengiriman as g', 'g.id_transaksi', '=', 't.id_transaksi')
            ->whereBetween('t.tanggal', [$dari->toDateString(), $sampai->toDateString()]);
    }

    private function item(Collection $ids): Collection
    {
        return DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->whereIn('d.id_transaksi', $ids)->orderBy('d.id_detail')
            ->get(['d.id_transaksi', 'p.nama_produk'])->groupBy('id_transaksi');
    }

    private function susun(string $mode, Carbon $dari, Carbon $sampai): array
    {
        $lunas = fn () => $this->dasar($dari, $sampai)->whereRaw(self::LUNAS);

        // Grafik: per hari (maks. 31 hari), selebihnya per bulan
        $jumlahHari = (int) $dari->diffInDays($sampai) + 1;
        $grafik = [];
        if ($jumlahHari <= 31) {
            $nilai = $lunas()->selectRaw('t.tanggal as k, SUM(b.jumlah) as nilai')->groupBy('t.tanggal')->pluck('nilai', 'k');
            for ($d = $dari->copy(); $d->lte($sampai); $d->addDay()) {
                $grafik[] = ['label' => $jumlahHari <= 7 ? self::HARI[$d->dayOfWeek] : $d->format('j'), 'nilai' => (float) ($nilai[$d->toDateString()] ?? 0)];
            }
        } else {
            $nilai = $lunas()->selectRaw("DATE_FORMAT(t.tanggal, '%Y-%m') as k, SUM(b.jumlah) as nilai")->groupBy('k')->pluck('nilai', 'k');
            for ($d = $dari->copy()->startOfMonth(); $d->lte($sampai); $d->addMonthNoOverflow()) {
                $grafik[] = ['label' => self::BULAN[$d->month - 1], 'nilai' => (float) ($nilai[$d->format('Y-m')] ?? 0)];
            }
        }
        $maks = max(collect($grafik)->max('nilai'), 1);

        $terjual = fn () => DB::table('detail_transaksi as d')
            ->join('transaksi as t', 't.id_transaksi', '=', 'd.id_transaksi')
            ->join('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->whereBetween('t.tanggal', [$dari->toDateString(), $sampai->toDateString()])->whereRaw(self::LUNAS);

        return [
            'mode' => $mode, 'dari' => $dari, 'sampai' => $sampai,
            'qs' => ['mode' => $mode, 'dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()],
            'ringkas' => [
                'total' => (float) $lunas()->sum('b.jumlah'),
                'transaksi' => $this->dasar($dari, $sampai)->count('t.id_transaksi'),
                'produk' => (int) $terjual()->sum('d.jumlah'),
                'selesai' => $this->dasar($dari, $sampai)->whereRaw("LOWER(COALESCE(g.status_pengiriman,'')) = 'selesai'")->count('t.id_transaksi'),
            ],
            'grafik' => $grafik, 'maks' => $maks, 'perBulan' => $jumlahHari > 31,
            'terlaris' => $terjual()->groupBy('p.id_produk', 'p.nama_produk', 'p.id_kategori', 'p.gambar')
                ->orderByDesc('terjual')->limit(5)
                ->get(['p.id_produk', 'p.nama_produk', 'p.id_kategori', 'p.gambar', DB::raw('SUM(d.jumlah) as terjual')]),
        ];
    }

    public function index(Request $r)
    {
        [$mode, $dari, $sampai] = $this->periode($r);
        $data = $this->susun($mode, $dari, $sampai);
        $urut = fn ($q) => $q->orderByDesc('t.tanggal')->orderByDesc('t.id_transaksi');

        if ($r->boolean('cetak')) {
            $rincian = $urut($this->dasar($dari, $sampai))->limit(2000)->get(self::KOLOM);
            return view('admin.laporan_cetak', $data + ['rincian' => $rincian, 'item' => $this->item($rincian->pluck('id_transaksi'))]);
        }
        $rincian = $urut($this->dasar($dari, $sampai))->paginate(15, self::KOLOM)->withQueryString();
        return view('admin.laporan', $data + ['rincian' => $rincian, 'item' => $this->item($rincian->pluck('id_transaksi'))]);
    }

    public function csv(Request $r)
    {
        [, $dari, $sampai] = $this->periode($r);
        $baris = $this->dasar($dari, $sampai)->orderBy('t.tanggal')->orderBy('t.id_transaksi')->limit(5000)->get(self::KOLOM);
        $item = $this->item($baris->pluck('id_transaksi'));
        // Cegah injeksi rumus saat dibuka di Excel/Sheets
        $aman = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;

        $status = fn ($v) => $v ? ucfirst(strtolower($v)) : '-';

        return response()->streamDownload(function () use ($baris, $item, $aman, $status) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF"); // BOM supaya huruf/aksen terbaca benar di Excel
            // Pemisah titik koma = bawaan Excel berbahasa Indonesia, jadi kolom tidak menumpuk dalam satu sel.
            $tulis = fn (array $kolom) => fputcsv($f, $kolom, ';', '"', '', "\r\n");
            $tulis(['No. Transaksi', 'Tanggal', 'Pelanggan', 'Item', 'Total (Rp)', 'Pembayaran', 'Pengiriman']);
            foreach ($baris as $b) {
                $tulis([
                    'TRX-' . str_pad($b->id_transaksi, 4, '0', STR_PAD_LEFT),
                    Carbon::parse($b->tanggal)->format('d/m/Y'),
                    $aman($b->nama),
                    $aman(($item[$b->id_transaksi] ?? collect())->pluck('nama_produk')->implode(', ')),
                    (int) $b->total,
                    $status($b->status_pembayaran),
                    $status($b->status_pengiriman),
                ]);
            }
            fclose($f);
        }, "laporan-penjualan-{$dari->toDateString()}_{$sampai->toDateString()}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
