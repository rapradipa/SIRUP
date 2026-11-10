<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PengirimanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

class PengirimanController extends Controller
{
    public function index()
    {
        $kirim = DB::table('pengiriman as g')
            ->join('transaksi as t', 't.id_transaksi', '=', 'g.id_transaksi')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->orderByRaw("FIELD(LOWER(g.status_pengiriman),'diproses','dikirim','menunggu','selesai')")
            ->orderByDesc('g.id_transaksi')
            ->paginate(10, ['g.id_transaksi', 'g.jenis', 'g.alamat_pengiriman', 'g.nama_penerima', 'g.status_pengiriman', 'g.kurir', 'g.no_resi', 'g.estimasi_tiba', 'p.nama']);
        return view('admin.pengiriman', compact('kirim'));
    }

    public function show(int $id)
    {
        $g = DB::table('pengiriman as g')
            ->join('transaksi as t', 't.id_transaksi', '=', 'g.id_transaksi')
            ->join('pelanggan as p', 'p.id_pelanggan', '=', 't.id_pelanggan')
            ->where('g.id_transaksi', $id)
            ->first(['g.*', 't.tanggal', 't.total', 'p.nama as pelanggan', 'p.no_telp as telp_pelanggan']);
        abort_if(! $g, 404);
        $items = DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('d.id_transaksi', $id)->orderBy('d.id_detail')->get(['d.jumlah', 'd.subtotal', 'p.nama_produk']);
        $log = DB::table('pengiriman_log')->where('id_transaksi', $id)->orderByDesc('id_log')->get();
        // Kurir dipilih dari staf (karyawan), bukan diketik bebas.
        $staf = DB::table('karyawan')->orderBy('nama')->get(['id_karyawan', 'nama']);
        return view('admin.pengiriman_detail', compact('g', 'items', 'log', 'staf'));
    }

    public function ubah(Request $r, int $id, PengirimanService $layanan)
    {
        $aksi = $r->input('aksi');
        try {
            if ($aksi === 'kirim') {
                $v = Validator::make($r->all(), [
                    'id_kurir' => 'required|exists:karyawan,id_karyawan', 'no_resi' => 'required|string|max:40', 'estimasi_tiba' => 'nullable|date|after_or_equal:today',
                ], ['id_kurir.required' => 'Pilih staf yang mengantar.', 'id_kurir.exists' => 'Staf yang dipilih tidak ditemukan.'], ['id_kurir' => 'Kurir', 'no_resi' => 'No. resi', 'estimasi_tiba' => 'Estimasi tiba']);
                if ($v->fails()) return back()->with('err', $v->errors()->first());
                $data = $v->validated();
                $data['kurir'] = DB::table('karyawan')->where('id_karyawan', $data['id_kurir'])->value('nama');
                unset($data['id_kurir']);
                $layanan->ubah($id, 'dikirim', $data);
            } elseif ($aksi === 'selesai') {
                $layanan->ubah($id, 'selesai');
            } else {
                abort(422);
            }
        } catch (RuntimeException $e) {
            return back()->with('err', $e->getMessage());
        }
        return back()->with('ok', 'Status pengiriman TRX-' . str_pad($id, 4, '0', STR_PAD_LEFT) . ' diperbarui.');
    }
}
