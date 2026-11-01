<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PembayaranService
{
    /**
     * Tandai pembayaran lunas dan kurangi stok (keputusan: stok berkurang saat LUNAS).
     * Semuanya dalam satu transaksi database: gagal di satu langkah = batal semua.
     */
    public function lunaskan(int $idTransaksi, ?int $idKaryawan): void
    {
        DB::transaction(function () use ($idTransaksi, $idKaryawan) {
            $b = DB::table('pembayaran')->where('id_transaksi', $idTransaksi)->lockForUpdate()->first();
            if (! $b) throw new RuntimeException('Data pembayaran tidak ditemukan.');
            if (strtolower($b->status_pembayaran) === 'lunas') throw new RuntimeException('Pembayaran ini sudah lunas.');

            $baris = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
                ->where('d.id_transaksi', $idTransaksi)->lockForUpdate()
                ->get(['d.jumlah', 'p.id_produk', 'p.nama_produk', 'p.stok']);

            foreach ($baris as $r) {
                if ($r->stok < $r->jumlah) {
                    throw new RuntimeException("Stok {$r->nama_produk} tidak cukup (sisa {$r->stok}, dibutuhkan {$r->jumlah}).");
                }
            }
            foreach ($baris as $r) {
                DB::table('produk')->where('id_produk', $r->id_produk)->decrement('stok', $r->jumlah);
            }
            DB::table('pembayaran')->where('id_pembayaran', $b->id_pembayaran)->update(['status_pembayaran' => 'lunas', 'dibayar_pada' => now()]);
            DB::table('transaksi')->where('id_transaksi', $idTransaksi)->update(['id_karyawan' => $idKaryawan]);
        });
    }
}
