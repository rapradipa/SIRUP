<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PengirimanService
{
    /** Pelanggan memilih kirim/ambil di toko. Hanya boleh setelah pembayaran lunas. */
    public function buat(int $idTransaksi, int $idPelanggan, array $d): void
    {
        DB::transaction(function () use ($idTransaksi, $idPelanggan, $d) {
            $b = DB::table('pembayaran as b')->join('transaksi as t', 't.id_transaksi', '=', 'b.id_transaksi')
                ->where('t.id_transaksi', $idTransaksi)->where('t.id_pelanggan', $idPelanggan)
                ->lockForUpdate()->first(['b.status_pembayaran']);
            if (! $b) throw new RuntimeException('Transaksi tidak ditemukan.');
            if (strtolower($b->status_pembayaran) !== 'lunas') throw new RuntimeException('Pengiriman baru bisa dipilih setelah pembayaran lunas.');
            if (DB::table('pengiriman')->where('id_transaksi', $idTransaksi)->lockForUpdate()->exists()) {
                throw new RuntimeException('Pilihan pengiriman untuk transaksi ini sudah tersimpan.');
            }

            $ambil = $d['jenis'] === 'ambil';
            DB::table('pengiriman')->insert([
                'id_transaksi' => $idTransaksi, 'jenis' => $d['jenis'], 'status_pengiriman' => 'diproses',
                'alamat_pengiriman' => $ambil ? 'Ambil di toko Raden Group' : "{$d['alamat']}, {$d['kota']} {$d['kode_pos']}",
                'nama_penerima' => $d['nama_penerima'] ?? null, 'no_telp_penerima' => $d['no_telp_penerima'] ?? null,
                'kota' => $d['kota'] ?? null, 'kode_pos' => $d['kode_pos'] ?? null,
            ]);
            $this->catat($idTransaksi, 'diproses', $ambil ? 'Pesanan disiapkan untuk diambil di toko' : 'Pesanan diproses dan dikemas oleh toko');
        });
    }

    /** Ubah status: diproses -> dikirim -> selesai (kirim) atau diproses -> selesai (ambil di toko). */
    public function ubah(int $idTransaksi, string $ke, array $x = []): void
    {
        DB::transaction(function () use ($idTransaksi, $ke, $x) {
            $g = DB::table('pengiriman')->where('id_transaksi', $idTransaksi)->lockForUpdate()->first();
            if (! $g) throw new RuntimeException('Data pengiriman belum ada.');

            $dari = strtolower($g->status_pengiriman); $ambil = $g->jenis === 'ambil';
            $boleh = $ambil ? ['diproses' => ['selesai']] : ['diproses' => ['dikirim'], 'dikirim' => ['selesai']];
            if (! in_array($ke, $boleh[$dari] ?? [], true)) {
                throw new RuntimeException("Status pengiriman tidak bisa diubah dari \"$dari\" ke \"$ke\".");
            }

            $set = ['status_pengiriman' => $ke];
            if ($ke === 'dikirim') $set += ['kurir' => $x['kurir'], 'no_resi' => $x['no_resi'], 'estimasi_tiba' => $x['estimasi_tiba'] ?? null];
            DB::table('pengiriman')->where('id_pengiriman', $g->id_pengiriman)->update($set);

            $ket = $ke === 'dikirim' ? "Diserahkan ke kurir {$x['kurir']} (resi {$x['no_resi']})"
                : ($x['catatan'] ?? ($ambil ? 'Pesanan sudah diambil di toko' : 'Pesanan diterima'));
            $this->catat($idTransaksi, $ke, $ket);
        });
    }

    private function catat(int $idTransaksi, string $status, string $ket): void
    {
        DB::table('pengiriman_log')->insert(['id_transaksi' => $idTransaksi, 'status' => $status, 'keterangan' => $ket]);
    }
}
