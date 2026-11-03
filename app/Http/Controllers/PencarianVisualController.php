<?php

namespace App\Http\Controllers;

use App\Services\PencarianVisualService;
use Illuminate\Http\Request;
use RuntimeException;

class PencarianVisualController extends Controller
{
    public function index()
    {
        return view('pelanggan.cari_foto', ['aktif' => (bool) config('groq.key')]);
    }

    public function proses(Request $r, PencarianVisualService $layanan)
    {
        $v = validator($r->all(), ['foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'], [
            'foto.required' => 'Pilih atau ambil foto barang terlebih dahulu.',
            'foto.image' => 'File harus berupa gambar.', 'foto.mimes' => 'Format gambar: JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.', 'foto.uploaded' => 'Foto gagal diunggah.',
        ]);
        if ($v->fails()) return response()->json(['pesan' => $v->errors()->first()], 422);

        // Foto hanya diproses di memori dan tidak disimpan di server toko.
        $file = $r->file('foto');
        $dataUrl = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));

        try {
            $hasil = $layanan->cari($dataUrl);
        } catch (RuntimeException $e) {
            return response()->json(['pesan' => $e->getMessage()], 503);
        }

        $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
        return response()->json([
            'terdeteksi' => $hasil['terdeteksi'],
            'hasil' => array_map(function ($h, $i) use ($rp) {
                $p = $h['p'];
                return [
                    'nama' => $p->nama_produk, 'kategori' => $p->nama_kategori, 'stok' => (int) $p->stok,
                    'harga' => $rp($p->harga * (100 - $p->diskon) / 100), 'diskon' => (int) $p->diskon,
                    'url' => route('produk', $p->id_produk), 'alasan' => $h['alasan'], 'utama' => $i === 0,
                ];
            }, $hasil['hasil'], array_keys($hasil['hasil'])),
        ]);
    }
}
