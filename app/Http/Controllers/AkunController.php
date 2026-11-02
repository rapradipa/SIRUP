<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunController extends Controller
{
    private function pid(): int
    {
        return (int) auth()->user()->id_pelanggan;
    }

    private function dasar()
    {
        return DB::table('transaksi as t')
            ->leftJoin('pembayaran as b', 'b.id_transaksi', '=', 't.id_transaksi')
            ->leftJoin('pengiriman as g', 'g.id_transaksi', '=', 't.id_transaksi')
            ->where('t.id_pelanggan', $this->pid());
    }

    public function profil()
    {
        $kirim = fn ($s) => $this->dasar()->whereRaw("LOWER(COALESCE(g.status_pengiriman,'')) = '$s'")->count();
        $status = [
            'menunggu' => $this->dasar()->whereRaw("LOWER(COALESCE(b.status_pembayaran,'')) <> 'lunas'")->count(),
            'diproses' => $kirim('diproses'), 'dikirim' => $kirim('dikirim'), 'selesai' => $kirim('selesai'),
        ];
        $terakhir = $this->dasar()->orderByDesc('t.id_transaksi')
            ->first(['t.id_transaksi', 't.tanggal', 't.total', 'b.status_pembayaran', 'g.status_pengiriman']);
        $item = $terakhir ? DB::table('detail_transaksi as d')->join('produk as p', 'p.id_produk', '=', 'd.id_produk')
            ->where('d.id_transaksi', $terakhir->id_transaksi)->orderBy('d.id_detail')->pluck('p.nama_produk') : collect();
        return view('pelanggan.akun', [
            'p' => DB::table('pelanggan')->where('id_pelanggan', $this->pid())->first(),
            'status' => $status, 'terakhir' => $terakhir, 'item' => $item,
        ]);
    }

    public function ubah()
    {
        return view('pelanggan.akun_ubah', ['p' => DB::table('pelanggan')->where('id_pelanggan', $this->pid())->first()]);
    }

    public function perbarui(Request $r)
    {
        $akun = auth()->user();
        $d = $r->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'unique:akun,no_telp,' . $akun->id_akun . ',id_akun'],
            'email' => 'nullable|email|max:120',
        ], Pesan::VALIDASI + ['email' => 'Format email tidak valid.', 'no_telp.unique' => 'Nomor ini sudah dipakai akun lain.', 'no_telp.regex' => 'Gunakan format 08xxxxxxxxxx.']);

        DB::transaction(function () use ($d, $akun) {
            DB::table('pelanggan')->where('id_pelanggan', $akun->id_pelanggan)
                ->update(['nama' => $d['nama'], 'no_telp' => $d['no_telp'], 'email' => $d['email'] ?? null]);
            DB::table('akun')->where('id_akun', $akun->id_akun)->update(['no_telp' => $d['no_telp']]);
        });
        return redirect()->route('akun')->with('ok', 'Biodata diperbarui.');
    }

    public function sandi(Request $r)
    {
        $akun = auth()->user();
        $d = $r->validateWithBag('sandi', [
            'sandi_lama' => 'required',
            'sandi_baru' => 'required|string|min:8|max:72|confirmed',
        ], [
            'sandi_lama.required' => 'Kata sandi lama wajib diisi.', 'sandi_baru.required' => 'Kata sandi baru wajib diisi.',
            'sandi_baru.min' => 'Kata sandi minimal 8 karakter.', 'sandi_baru.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);
        if (! Hash::check($d['sandi_lama'], $akun->password)) {
            return back()->withErrors(['sandi_lama' => 'Kata sandi lama salah.'], 'sandi');
        }
        Akun::where('id_akun', $akun->id_akun)->firstOrFail()->update(['password' => $d['sandi_baru']]);
        $r->session()->regenerate();
        return redirect()->route('akun.ubah')->with('ok', 'Kata sandi diperbarui.');
    }
}
