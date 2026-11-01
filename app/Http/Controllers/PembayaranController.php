<?php

namespace App\Http\Controllers;

use App\Services\PengirimanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PembayaranController extends Controller
{
    private function muat(int $id)
    {
        $t = DB::table('transaksi as t')->join('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->where('t.id_transaksi', $id)->where('t.id_pelanggan', auth()->user()->id_pelanggan)
            ->first(['t.id_transaksi', 't.tanggal', 't.total', 'b.status_pembayaran', 'b.metode']);
        abort_if(! $t, 404);
        return $t;
    }

    public function show(int $id)
    {
        $t = $this->muat($id);
        $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('d.id_transaksi', $id)->get(['d.jumlah', 'd.subtotal', 'p.nama_produk', 'p.id_kategori', 'p.gambar']);
        $g = DB::table('pengiriman')->where('id_transaksi', $id)->first();
        return view('pelanggan.pembayaran', compact('t', 'items', 'g'));
    }

    public function bayar(Request $r, int $id)
    {
        $t = $this->muat($id);
        if (strtolower($t->status_pembayaran) === 'lunas') return back()->with('err', 'Transaksi ini sudah lunas.');
        $d = $r->validate(['metode' => 'required|in:tunai,transfer,ewallet'], ['metode.required' => 'Pilih metode pembayaran.', 'metode.in' => 'Metode tidak valid.']);
        DB::table('pembayaran')->where('id_transaksi', $id)->update(['metode' => $d['metode']]);
        return back()->with('ok', 'Metode pembayaran dicatat. Menunggu verifikasi dari kasir.');
    }

    public function alamat(Request $r, int $id, PengirimanService $layanan)
    {
        $this->muat($id);
        $d = ['jenis' => $r->input('jenis') === 'ambil' ? 'ambil' : 'kirim'];
        if ($d['jenis'] === 'kirim') {
            $d += $r->validate([
                'nama_penerima' => 'required|string|max:100',
                'no_telp_penerima' => ['required', 'regex:/^08[0-9]{8,13}$/'],
                'alamat' => 'required|string|max:255',
                'kota' => 'required|string|max:100',
                'kode_pos' => ['required', 'regex:/^[0-9]{5}$/'],
            ], [
                'required' => 'Kolom ini wajib diisi.', 'no_telp_penerima.regex' => 'Gunakan format 08xxxxxxxxxx.',
                'kode_pos.regex' => 'Kode pos terdiri dari 5 angka.', 'max' => 'Terlalu panjang.',
            ]);
        }
        try {
            $layanan->buat($id, (int) auth()->user()->id_pelanggan, $d);
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }
        return redirect()->route('pembayaran', $id)->with('ok', $d['jenis'] === 'ambil'
            ? 'Pesanan akan disiapkan untuk diambil di toko.' : 'Alamat disimpan. Pesanan Anda segera diproses.');
    }
}
