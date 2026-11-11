<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PembayaranService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransaksiController extends Controller
{
    public function index(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 30);
        // Format yang diterima: "TRX-0007", "trx7", "0007" atau "7" (boleh sebagian, mis. "12")
        $angka = null;
        if ($q !== '' && preg_match('/^(?:trx[\s-]*)?(\d+)$/i', $q, $m)) {
            $angka = ltrim($m[1], '0') ?: '0';
        }

        $transaksi = DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->when($q !== '' && $angka === null, fn ($b) => $b->whereRaw('1 = 0'))
            ->when($angka !== null, fn ($b) => $b->whereRaw('CAST(t.id_transaksi AS CHAR) like ?', ['%' . $angka . '%']))
            ->orderByDesc('t.id_transaksi')
            ->paginate(12, ['t.id_transaksi', 't.tanggal', 't.total', 'p.nama', 'b.status_pembayaran', 'b.metode', 'b.bank', 'b.bukti'])
            ->withQueryString();
        return view('admin.transaksi', compact('transaksi', 'q'));
    }

    public function lunas(int $id, PembayaranService $layanan)
    {
        try {
            $layanan->lunaskan($id, auth()->user()->id_karyawan);
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }
        return back()->with('ok', 'TRX-' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' ditandai lunas dan stok sudah dikurangi.');
    }
}
