<?php

namespace App\Http\Controllers;

use App\Support\Pesan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlamatController extends Controller
{
    private function pid(): int
    {
        return (int) auth()->user()->id_pelanggan;
    }

    private function milik(int $id)
    {
        $a = DB::table('alamat')->where('id_alamat', $id)->where('id_pelanggan', $this->pid())->first();
        abort_if(! $a, 404);
        return $a;
    }

    public function index()
    {
        return view('pelanggan.alamat', ['alamat' => DB::table('alamat')->where('id_pelanggan', $this->pid())->orderByDesc('utama')->orderBy('id_alamat')->get()]);
    }

    public function baru()
    {
        return view('pelanggan.alamat_form', ['a' => null]);
    }

    public function ubah(int $id)
    {
        return view('pelanggan.alamat_form', ['a' => $this->milik($id)]);
    }

    private function validasi(Request $r): array
    {
        return $r->validate([
            'label' => 'required|string|max:40',
            'nama_penerima' => 'required|string|max:100',
            'no_telp' => ['required', 'regex:/^08[0-9]{8,13}$/'],
            'alamat' => 'required|string|max:255',
            'kota' => 'required|string|max:100',
            'kode_pos' => ['required', 'regex:/^[0-9]{5}$/'],
        ], Pesan::VALIDASI + ['no_telp.regex' => 'Gunakan format 08xxxxxxxxxx.', 'kode_pos.regex' => 'Kode pos terdiri dari 5 angka.']);
    }

    public function simpan(Request $r)
    {
        $d = $this->validasi($r); $pid = $this->pid();
        DB::transaction(function () use ($d, $r, $pid) {
            $pertama = ! DB::table('alamat')->where('id_pelanggan', $pid)->exists();
            $utama = $pertama || $r->boolean('utama');
            if ($utama) DB::table('alamat')->where('id_pelanggan', $pid)->update(['utama' => false]);
            DB::table('alamat')->insert($d + ['id_pelanggan' => $pid, 'utama' => $utama]);
        });
        return redirect()->route('akun.alamat')->with('ok', 'Alamat ditambahkan.');
    }

    public function perbarui(Request $r, int $id)
    {
        $a = $this->milik($id); $d = $this->validasi($r); $pid = $this->pid();
        DB::transaction(function () use ($d, $r, $a, $pid) {
            $utama = $a->utama || $r->boolean('utama');
            if ($utama && ! $a->utama) DB::table('alamat')->where('id_pelanggan', $pid)->update(['utama' => false]);
            DB::table('alamat')->where('id_alamat', $a->id_alamat)->update($d + ['utama' => $utama]);
        });
        return redirect()->route('akun.alamat')->with('ok', 'Alamat diperbarui.');
    }

    public function utama(int $id)
    {
        $a = $this->milik($id);
        DB::transaction(function () use ($a) {
            DB::table('alamat')->where('id_pelanggan', $a->id_pelanggan)->update(['utama' => false]);
            DB::table('alamat')->where('id_alamat', $a->id_alamat)->update(['utama' => true]);
        });
        return back()->with('ok', "\"{$a->label}\" dijadikan alamat utama.");
    }

    public function hapus(int $id)
    {
        $a = $this->milik($id);
        DB::transaction(function () use ($a) {
            DB::table('alamat')->where('id_alamat', $a->id_alamat)->delete();
            if ($a->utama) { // alihkan status utama ke alamat lain bila ada
                $lain = DB::table('alamat')->where('id_pelanggan', $a->id_pelanggan)->orderBy('id_alamat')->value('id_alamat');
                if ($lain) DB::table('alamat')->where('id_alamat', $lain)->update(['utama' => true]);
            }
        });
        return back()->with('ok', "Alamat \"{$a->label}\" dihapus.");
    }
}
