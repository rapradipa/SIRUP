@extends('layouts.admin')
@section('title', 'Tambah Staf')
@section('content')
<form class="panel" style="max-width:620px" method="post" action="{{ route('admin.pengguna.simpan') }}" novalidate>
  @csrf
  <p class="crumb"><a href="{{ route('admin.pengguna') }}">Pengguna</a> &rsaquo; <span>Tambah Staf</span></p>
  <h2>Tambah Akun Staf</h2>
  <div class="f" style="margin-top:14px"><label for="nama">Nama lengkap</label>
    <input type="text" id="nama" name="nama" value="{{ old('nama') }}" maxlength="100" class="{{ $errors->has('nama') ? 'bad' : '' }}">
    @error('nama')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="f"><label for="no_telp">Nomor telepon (untuk login)</label>
    <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" inputmode="numeric" placeholder="08xxxxxxxxxx" class="{{ $errors->has('no_telp') ? 'bad' : '' }}">
    @error('no_telp')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="f"><label for="role">Peran</label>
    <select id="role" name="role" class="{{ $errors->has('role') ? 'bad' : '' }}">
      <option value="kasir" @selected(old('role', 'kasir') === 'kasir')>Kasir (transaksi, pembayaran, pengiriman, stok masuk)</option>
      <option value="admin" @selected(old('role') === 'admin')>Admin (akses penuh)</option>
    </select>
    @error('role')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="f"><label for="password">Kata sandi awal</label>
    <input type="password" id="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="{{ $errors->has('password') ? 'bad' : '' }}">
    @error('password')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="fa"><a class="btn out" href="{{ route('admin.pengguna') }}">Batal</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan</button></div>
</form>
@endsection
