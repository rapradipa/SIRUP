<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenggunaController extends Controller
{
    public function index(Request $r)
    {
        $q = mb_substr(trim((string) $r->query('q', '')), 0, 50);
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $akun = DB::table('akun as a')
            ->leftJoin('karyawan as k', 'k.id_karyawan', '=', 'a.id_karyawan')
            ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'a.id_pelanggan')
            ->when($q !== '', fn ($b) => $b->where(fn ($w) => $w
                ->whereRaw('COALESCE(k.nama, p.nama) like ?', [$like])   // nama
                ->orWhere('a.no_telp', 'like', $like)))                   // atau no. telepon
            ->orderByRaw("FIELD(a.role,'admin','kasir','pelanggan')")->orderBy('a.id_akun')
            ->paginate(12, ['a.id_akun', 'a.no_telp', 'a.role', DB::raw('COALESCE(k.nama, p.nama) as nama')])
            ->withQueryString();
        return view('admin.pengguna', compact('akun', 'q'));
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

    public function show(int $id)
    {
        $a = DB::table('akun as a')
            ->leftJoin('karyawan as k', 'k.id_karyawan', '=', 'a.id_karyawan')
            ->leftJoin('pelanggan as p', 'p.id_pelanggan', '=', 'a.id_pelanggan')
            ->where('a.id_akun', $id)
            ->first(['a.id_akun', 'a.no_telp', 'a.role', DB::raw('COALESCE(k.nama, p.nama) as nama')]);
        abort_if(! $a, 404);
        return view('admin.pengguna_detail', compact('a'));
    }

    public function sandi(Request $r, int $id)
    {
        $akun = Akun::where('id_akun', $id)->firstOrFail();
        $d = $r->validate(['password' => 'required|string|min:8|max:72|confirmed'], [
            'required' => 'Isi kata sandi baru.', 'min' => 'Kata sandi minimal 8 karakter.', 'confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);
        $akun->update(['password' => $d['password']]);
        return redirect()->route('admin.pengguna.show', $id)->with('ok', 'Kata sandi akun berhasil diubah.');
    }

    /** Alamat daftar pengguna (lengkap dengan halaman) tempat pengguna datang; aman dari open-redirect. */
    private function kembali(Request $r): string
    {
        $u = (string) ($r->input('kembali') ?? '');
        $path = parse_url(route('admin.pengguna'), PHP_URL_PATH);
        $aman = str_starts_with($u, '/') && ! str_starts_with($u, '//') && ($u === $path || str_starts_with($u, $path . '?'));
        return $aman ? $u : route('admin.pengguna');
    }

    /**
     * Hapus akun pengguna.
     * - Staf (admin/kasir): akun dihapus; data karyawan ikut dihapus bila tidak punya transaksi.
     * - Pelanggan: akun, keranjang, dan buku alamatnya dihapus. Bila pernah bertransaksi, data pelanggan
     *   dipertahankan agar riwayat transaksi/invoice tetap utuh; bila belum, seluruh datanya dihapus.
     */
    public function hapus(Request $r, int $id)
    {
        $a = Akun::where('id_akun', $id)->firstOrFail();
        $tujuan = $this->kembali($r);

        if ($a->id_akun === auth()->id()) {
            return redirect($tujuan)->with('err', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }
        if ($a->role === 'admin' && Akun::where('role', 'admin')->count() <= 1) {
            return redirect($tujuan)->with('err', 'Harus tersisa minimal satu admin.');
        }

        $pesan = DB::transaction(function () use ($a) {
            if ($a->id_pelanggan) {
                $idP = $a->id_pelanggan;
                $adaTransaksi = DB::table('transaksi')->where('id_pelanggan', $idP)->exists();
                DB::table('keranjang')->where('id_pelanggan', $idP)->delete();
                DB::table('alamat')->where('id_pelanggan', $idP)->delete();
                $a->delete();
                if (! $adaTransaksi) {
                    DB::table('pelanggan')->where('id_pelanggan', $idP)->delete();
                    return 'Akun pelanggan beserta datanya dihapus.';
                }
                return 'Akun pelanggan dihapus. Riwayat transaksinya tetap tersimpan.';
            }

            $idK = $a->id_karyawan;
            $a->delete();
            if ($idK && ! DB::table('transaksi')->where('id_karyawan', $idK)->exists()) {
                DB::table('karyawan')->where('id_karyawan', $idK)->delete();
            }
            return 'Akun ' . $a->role . ' dihapus.';
        });

        // Keluarkan pengguna tersebut jika sedang login di perangkat lain
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $a->id_akun)->delete();
        }
        return redirect($tujuan)->with('ok', $pesan);
    }
}
