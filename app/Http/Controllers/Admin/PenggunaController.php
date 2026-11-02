<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenggunaController extends Controller
{
    public function index()
    {
        $akun = DB::table('akun as a')
            ->leftJoin('karyawan as k', 'k.id_karyawan', '=', 'a.id_karyawan')
            ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'a.id_pelanggan')
            ->orderByRaw("FIELD(a.role,'admin','kasir','pelanggan')")->orderBy('a.id_akun')
            ->paginate(12, ['a.id_akun', 'a.no_telp', 'a.role', DB::raw('COALESCE(k.nama, p.nama) as nama')]);
        return view('admin.pengguna', compact('akun'));
    }

    public function baru()
    {
        return view('admin.pengguna_form');
    }

    public function simpan(Request $r)
    {
        $d = $r->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'unique:akun,no_telp'],
            'role' => 'required|in:admin,kasir',
            'password' => 'required|string|min:8|max:72',
        ], Pesan::VALIDASI + ['no_telp.unique' => 'Nomor ini sudah terdaftar.', 'no_telp.regex' => 'Gunakan format 08xxxxxxxxxx.', 'role.in' => 'Peran tidak valid.', 'password.min' => 'Kata sandi minimal 8 karakter.']);

        DB::transaction(function () use ($d) {
            $idKaryawan = DB::table('karyawan')->insertGetId(['nama' => $d['nama'], 'no_telp' => $d['no_telp']]);
            Akun::create(['no_telp' => $d['no_telp'], 'password' => $d['password'], 'role' => $d['role'], 'id_karyawan' => $idKaryawan]);
        });
        return redirect()->route('admin.pengguna')->with('ok', "Akun {$d['role']} \"{$d['nama']}\" dibuat.");
    }

    public function sandi(Request $r, int $id)
    {
        $d = $r->validate(['password' => 'required|string|min:8|max:72'], ['required' => 'Isi kata sandi baru.', 'min' => 'Kata sandi minimal 8 karakter.']);
        Akun::where('id_akun', $id)->firstOrFail()->update(['password' => $d['password']]);
        return back()->with('ok', 'Kata sandi akun diatur ulang.');
    }

    public function hapus(int $id)
    {
        $a = Akun::where('id_akun', $id)->firstOrFail();
        if ($a->id_akun === auth()->id()) return back()->with('err', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        if ($a->role === 'pelanggan') return back()->with('err', 'Akun pelanggan tidak dihapus dari sini karena terkait riwayat transaksi.');
        if ($a->role === 'admin' && Akun::where('role', 'admin')->count() <= 1) return back()->with('err', 'Harus tersisa minimal satu admin.');
        $a->delete(); // hanya akun login; data karyawan tetap ada untuk riwayat transaksi
        return back()->with('ok', 'Akun login dihapus.');
    }
}
