<?php

namespace App\Http\Controllers;

use App\Services\PengirimanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PembayaranController extends Controller
{
    private const FOLDER = 'uploads/bukti';

    private function muat(int $id)
    {
        $t = DB::table('transaksi as t')->join('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->where('t.id_transaksi', $id)->where('t.id_pelanggan', auth()->user()->id_pelanggan)
            ->first(['t.id_transaksi', 't.tanggal', 't.total', 'b.status_pembayaran', 'b.metode', 'b.bank', 'b.bukti']);
        abort_if(! $t, 404);
        return $t;
    }

    public function show(int $id)
    {
        $t = $this->muat($id);
        $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('d.id_transaksi', $id)->get(['d.jumlah', 'd.subtotal', 'p.nama_produk', 'p.id_kategori', 'p.gambar']);
        $g = DB::table('pengiriman')->where('id_transaksi', $id)->first();
        $tersimpan = DB::table('alamat')->where('id_pelanggan', auth()->user()->id_pelanggan)->orderByDesc('utama')->orderBy('id_alamat')->get();
        return view('pelanggan.pembayaran', compact('t', 'items', 'g', 'tersimpan'));
    }

    public function bayar(Request $r, int $id)
    {
        $t = $this->muat($id);
        if (strtolower($t->status_pembayaran) === 'lunas') return back()->with('err', 'Transaksi ini sudah lunas.');
        $d = $r->validate([
            'metode' => 'required|in:tunai,transfer,qris',
            'bank' => 'required_if:metode,transfer|nullable|in:bri,mandiri',
        ], [
            'metode.required' => 'Pilih metode pembayaran.', 'metode.in' => 'Metode tidak valid.',
            'bank.required_if' => 'Pilih bank tujuan transfer.', 'bank.in' => 'Bank tidak valid.',
        ]);
        $bank = $d['metode'] === 'transfer' ? $d['bank'] : null;
        // Ganti metode atau bank membatalkan bukti lama, karena bukti yang tersimpan mungkin sudah tidak relevan.
        if ($d['metode'] !== $t->metode || $bank !== $t->bank) {
            $this->hapusBukti($t->bukti);
            DB::table('pembayaran')->where('id_transaksi', $id)->update(['metode' => $d['metode'], 'bank' => $bank, 'bukti' => null]);
        }
        return back()->with('ok', match (true) {
            $d['metode'] === 'qris' => 'Silakan pindai kode QRIS, lalu kirim bukti pembayaran di bawah ini.',
            $d['metode'] === 'transfer' => 'Silakan transfer ke rekening tujuan, lalu kirim bukti pembayaran di bawah ini.',
            default => 'Metode pembayaran dicatat. Menunggu verifikasi dari kasir.',
        });
    }

    public function bukti(Request $r, int $id)
    {
        $t = $this->muat($id);
        if (strtolower($t->status_pembayaran) === 'lunas') return back()->with('err', 'Transaksi ini sudah lunas.');
        if (! in_array($t->metode, ['qris', 'transfer'], true)) return back()->with('err', 'Pilih metode QRIS atau Transfer Bank terlebih dahulu.');
        $r->validate(['bukti' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'],
            ['bukti.required' => 'Unggah bukti pembayaran terlebih dahulu.', 'bukti.image' => 'File harus berupa gambar.', 'bukti.max' => 'Ukuran gambar maksimal 2 MB.']);

        $this->hapusBukti($t->bukti);
        File::ensureDirectoryExists(public_path(self::FOLDER));
        $nama = Str::random(24) . '.' . $r->file('bukti')->extension();
        $r->file('bukti')->move(public_path(self::FOLDER), $nama);

        DB::table('pembayaran')->where('id_transaksi', $id)->update(['bukti' => $nama]);
        return back()->with('ok', 'Bukti pembayaran terkirim. Menunggu verifikasi dari kasir.');
    }

    private function hapusBukti(?string $nama): void
    {
        if ($nama) File::delete(public_path(self::FOLDER . '/' . basename($nama)));
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
