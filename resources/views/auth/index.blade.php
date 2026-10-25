@extends('layouts.app')
@section('title', $tab === 'daftar' ? 'Daftar - Raden Group' : 'Masuk - Raden Group')
@section('content')
@php
  $daftar = $tab === 'daftar' || old('nama') !== null;
@endphp
<div class="box">
  <div class="tabs">
    <a href="{{ route('login') }}" class="{{ $daftar ? '' : 'on' }}">Masuk</a>
    <a href="{{ route('login', ['tab' => 'daftar']) }}" class="{{ $daftar ? 'on' : '' }}">Daftar</a>
  </div>

  @if ($daftar)
  <h1>Daftar Akun Pelanggan</h1>
  <form method="post" action="{{ route('register.proses') }}" novalidate>
    @csrf
    <div class="f"><label for="nama">Nama lengkap</label>
      <input type="text" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap" autocomplete="name" class="{{ $errors->has('nama') ? 'bad' : '' }}">
      @error('nama')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="no_telp">Nomor telepon</label>
      <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" inputmode="numeric" class="{{ $errors->has('no_telp') ? 'bad' : '' }}">
      @error('no_telp')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="password">Kata sandi</label>
      <div class="pw"><input type="password" id="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" class="{{ $errors->has('password') ? 'bad' : '' }}"><button type="button" data-lihat="password" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" class="on" /><x-ikon n="eyeoff" class="off" /></button></div>
      @error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <button class="btn full" style="margin-top:6px">Daftar Sekarang</button>
    <p class="sw">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
  </form>
  @else
  <h1>Masuk ke Akun Anda</h1>
  <form method="post" action="{{ route('login.proses') }}" novalidate>
    @csrf
    <div class="f"><label for="no_telp">Nomor telepon</label>
      <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" inputmode="numeric" autofocus class="{{ $errors->has('no_telp') ? 'bad' : '' }}">
      @error('no_telp')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="password">Kata sandi</label>
      <div class="pw"><input type="password" id="password" name="password" placeholder="Masukkan kata sandi" autocomplete="current-password" class="{{ $errors->has('password') ? 'bad' : '' }}"><button type="button" data-lihat="password" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" class="on" /><x-ikon n="eyeoff" class="off" /></button></div>
      @error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="row">
      <label><input type="checkbox" name="ingat" value="1"> Ingat saya</label>
      <a href="#" id="lupa">Lupa kata sandi?</a>
    </div>
    <button class="btn full">Masuk</button>
    <p class="sw">Belum punya akun? <a href="{{ route('login', ['tab' => 'daftar']) }}">Daftar</a></p>
  </form>
  @endif
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-lihat]').forEach(b => b.addEventListener('click', () => {
  const i = document.getElementById(b.dataset.lihat), w = b.parentElement, s = i.type === 'password';
  i.type = s ? 'text' : 'password'; w.classList.toggle('show', s);
}));
const lupa = document.getElementById('lupa');
if (lupa) lupa.addEventListener('click', e => { e.preventDefault(); alert('Untuk mengatur ulang kata sandi, silakan hubungi admin toko Raden Group.'); });
</script>
@endpush
