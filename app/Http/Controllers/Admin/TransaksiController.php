<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PembayaranService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = DB::table('transaksi as t')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->orderByDesc('t.id_transaksi')
            ->paginate(12, ['t.id_transaksi', 't.tanggal', 't.total', 'p.nama', 'b.status_pembayaran', 'b.metode', 'b.bank', 'b.bukti']);
        return view('admin.transaksi', compact('transaksi'));
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
