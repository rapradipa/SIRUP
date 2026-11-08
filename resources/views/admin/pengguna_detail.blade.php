@extends('layouts.admin')
@section('title', 'Kelola Akun')
@section('content')
@php $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g']; @endphp
<div style="max-width:620px;display:grid;gap:20px">
  <p class="crumb" style="margin:0"><a href="{{ route('admin.pengguna') }}">Pengguna</a> &rsaquo; <span>{{ $a->nama }}</span></p>

  <section class="panel pfh">
    <span class="av xl">{{ strtoupper(substr($a->nama ?? '?', 0, 1)) }}</span>
    <div class="pfi">
      <h1 style="font-size:22px">{{ $a->nama ?? '-' }}</h1>
      <div class="mu">{{ $a->no_telp }}</div>
      <span class="bd {{ $kls[$a->role] ?? 'b' }}">{{ ucfirst($a->role) }}</span>
      @if ($a->id_akun === auth()->id()) <span class="mu">(akun Anda)</span>@endif
    </div>
  </section>

  <form class="panel" method="post" action="{{ route('admin.pengguna.sandi', $a->id_akun) }}" novalidate
        data-confirm="Ubah kata sandi akun ini? Pengguna harus memakai kata sandi baru untuk masuk.">
    @csrf @method('PUT')
    <h2><x-ikon n="lock" :s="18" style="vertical-align:-3px" /> Ubah Kata Sandi</h2>
    <div class="f" style="margin-top:14px"><label for="password">Kata sandi baru</label>
      <div class="pw"><input type="password" id="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="{{ $errors->has('password') ? 'bad' : '' }}"><button type="button" data-lihat="password" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div>
      @error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="password_confirmation">Konfirmasi kata sandi baru</label>
      <div class="pw"><input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"><button type="button" data-lihat="password_confirmation" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div></div>
    <div class="fa"><a class="btn out" href="{{ route('admin.pengguna') }}">Kembali</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan Kata Sandi</button></div>
  </form>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-lihat]').forEach(b => b.addEventListener('click', () => {
  const i = document.getElementById(b.dataset.lihat); i.type = i.type === 'password' ? 'text' : 'password';
}));
</script>
@endpush
