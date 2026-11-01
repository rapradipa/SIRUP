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

    public function ubah(Request $r, int $id, PengirimanService $layanan)
    {
        $aksi = $r->input('aksi');
        try {
            if ($aksi === 'kirim') {
                $v = Validator::make($r->all(), [
                    'kurir' => 'required|string|max:60', 'no_resi' => 'required|string|max:40', 'estimasi_tiba' => 'nullable|date|after_or_equal:today',
                ], [], ['kurir' => 'Kurir', 'no_resi' => 'No. resi', 'estimasi_tiba' => 'Estimasi tiba']);
                if ($v->fails()) return back()->with('err', $v->errors()->first());
                $layanan->ubah($id, 'dikirim', $v->validated());
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
