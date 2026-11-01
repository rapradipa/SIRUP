<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Models\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    private const TELP = ['required', 'regex:/^08[0-9]{8,13}$/'];

    public function show(Request $r)
    {
        return view('auth.index', ['tab' => $r->query('tab') === 'daftar' ? 'daftar' : 'masuk']);
    }

    public function login(Request $r)
    {
        $data = $r->validate(['no_telp' => self::TELP, 'password' => 'required'], [
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.regex' => 'Gunakan format 08xxxxxxxxxx.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (! Auth::attempt($data, $r->boolean('ingat'))) {
            return back()->withInput($r->only('no_telp'))->withErrors(['no_telp' => 'Nomor telepon atau kata sandi salah.']);
        }
        $r->session()->regenerate();
        return redirect()->intended('/');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'nama' => 'required|string|max:100',
            'no_telp' => [...self::TELP, 'unique:akun,no_telp'],
            'password' => 'required|string|min:8|max:72',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'no_telp.required' => 'Nomor telepon wajib diisi.',
            'no_telp.regex' => 'Gunakan format 08xxxxxxxxxx.',
            'no_telp.unique' => 'Nomor ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        $akun = DB::transaction(function () use ($data) {
            $p = Pelanggan::create(['nama' => $data['nama'], 'no_telp' => $data['no_telp']]);
            return Akun::create(['no_telp' => $data['no_telp'], 'password' => $data['password'],
                'role' => 'pelanggan', 'id_pelanggan' => $p->id_pelanggan]);
        });

        Auth::login($akun);
        $r->session()->regenerate();
        return redirect('/');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect()->route('beranda');
    }
}
