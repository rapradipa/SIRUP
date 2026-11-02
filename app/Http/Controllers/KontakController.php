<?php

namespace App\Http\Controllers;

class KontakController extends Controller
{
    public function index()
    {
        $wa = preg_replace('/\D+/', '', (string) config('toko.whatsapp'));
        if ($wa !== '' && str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);

        $topik = [
            'Status pesanan' => 'Halo Raden Group, saya ingin menanyakan status pesanan saya.',
            'Pembayaran' => 'Halo Raden Group, saya ingin bertanya tentang pembayaran.',
            'Pengiriman' => 'Halo Raden Group, saya ingin bertanya tentang pengiriman pesanan.',
            'Stok barang' => 'Halo Raden Group, saya ingin menanyakan ketersediaan stok barang.',
        ];
        $faq = [
            ['Bagaimana cara melacak pesanan?', 'Buka Pesanan Saya, lalu klik Lacak pada pesanan yang sudah dikirim. Anda juga bisa memasukkan nomor transaksi atau nomor resi di halaman Lacak Pengiriman.'],
            ['Bagaimana cara mengubah alamat?', 'Alamat pengiriman diisi setelah pembayaran lunas dan tidak dapat diubah sendiri setelah disimpan. Hubungi kami lewat WhatsApp secepatnya agar pesanan belum dikirim. Untuk pesanan berikutnya, simpan alamat Anda di menu Daftar Alamat.'],
            ['Bagaimana cara membayar?', 'Setelah checkout, pilih metode pembayaran (tunai di toko, transfer bank, atau e-wallet). Kasir akan memverifikasi pembayaran Anda, lalu status pesanan berubah menjadi Lunas.'],
        ];
        return view('pelanggan.kontak', [
            'toko' => config('toko'), 'wa' => $wa, 'topik' => $topik, 'faq' => $faq,
            'waTampil' => $wa !== '' ? '+' . substr($wa, 0, 2) . ' ' . substr($wa, 2) : null,
        ]);
    }
}
