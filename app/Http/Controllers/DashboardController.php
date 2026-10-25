<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private const HARI = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    private const BATAS_MENIPIS = 10;

    private function lunas()
    {
        return DB::table('pembayaran as b')
            ->join('transaksi as t', 't.id_transaksi', '=', 'b.id_transaksi')
            ->whereRaw("LOWER(b.status_pembayaran) = 'lunas'");
    }

    public function index()
    {
        // Minggu berjalan, Senin sampai Minggu (urutan sesuai desain)
        $grafik = collect(range(0, 6))->map(function ($i) {
            $d = today()->startOfWeek()->addDays($i);
            return ['label' => self::HARI[$d->dayOfWeek], 'nilai' => (float) $this->lunas()->whereDate('t.tanggal', $d)->sum('b.jumlah')];
        });

        $menipis = DB::table('produk')->where('stok', '<=', self::BATAS_MENIPIS);

        return view('admin.dashboard', [
            'penjualanHariIni' => (float) $this->lunas()->whereDate('t.tanggal', today())->sum('b.jumlah'),
            'transaksiHariIni' => DB::table('transaksi')->whereDate('tanggal', today())->count(),
            'pengirimanAktif' => DB::table('pengiriman')->whereRaw("LOWER(status_pengiriman) IN ('diproses','dikirim')")->count(),
            'jumlahMenipis' => (clone $menipis)->count(),
            'menipis' => $menipis->orderBy('stok')->limit(4)->get(),
            'grafik' => $grafik,
            'maks' => max($grafik->max('nilai'), 1),
            'terbaru' => DB::table('transaksi as t')
                ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
                ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
                ->leftJoin('pengiriman as g', 'g.id_transaksi', '=', 't.id_transaksi')
                ->orderByDesc('t.id_transaksi')->limit(5)
                ->get(['t.id_transaksi', 't.tanggal', 't.total', 'p.nama', 'b.status_pembayaran', 'g.status_pengiriman']),
        ]);
    }
}
