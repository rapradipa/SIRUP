@extends('layouts.app')
@section('title', 'Biodata & Keamanan - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('akun') }}">Akun Saya</a> &rsaquo; <span>Edit Profil</span></nav>
  <div class="ak">
    @include('pelanggan._akun_menu')
    <div style="display:grid;gap:20px">
      <form class="panel" method="post" action="{{ route('akun.perbarui') }}" novalidate>
        @csrf @method('PUT')
        <h2>Biodata Diri</h2>
        <div class="bio">
          <div class="bio-a"><span class="av xl">{{ strtoupper(substr($p->nama, 0, 1)) }}</span><small class="mu">Foto profil menyusul</small></div>
          <div>
            <div class="f"><label for="nama">Nama lengkap</label>
              <input type="text" id="nama" name="nama" value="{{ old('nama', $p->nama) }}" maxlength="100" required class="{{ $errors->has('nama') ? 'bad' : '' }}">
              @error('nama')<div class="err">{{ $message }}</div>@enderror</div>
            <div class="f"><label for="no_telp">Nomor telepon</label>
              <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', $p->no_telp) }}" inputmode="numeric" required class="{{ $errors->has('no_telp') ? 'bad' : '' }}">
              @error('no_telp')<div class="err">{{ $message }}</div>@enderror
              <div class="mu" style="margin-top:6px">Nomor ini juga dipakai untuk masuk ke akun Anda.</div></div>
            <div class="f"><label for="email">Email (opsional)</label>
              <input type="text" id="email" name="email" value="{{ old('email', $p->email) }}" maxlength="120" placeholder="nama@email.com" class="{{ $errors->has('email') ? 'bad' : '' }}">
              @error('email')<div class="err">{{ $message }}</div>@enderror</div>
          </div>
        </div>
        <div class="fa"><a class="btn out" href="{{ route('akun') }}">Batal</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan Perubahan</button></div>
      </form>

      <form class="panel" method="post" action="{{ route('akun.sandi') }}" novalidate>
        @csrf @method('PUT')
        <h2><x-ikon n="lock" :s="18" style="vertical-align:-3px" /> Ubah Kata Sandi</h2>
        <div class="fg3" style="margin-top:14px">
          <div class="f"><label for="sandi_lama">Kata sandi lama</label>
            <div class="pw"><input type="password" id="sandi_lama" name="sandi_lama" autocomplete="current-password" class="{{ $errors->sandi->has('sandi_lama') ? 'bad' : '' }}"><button type="button" data-lihat="sandi_lama" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div>
            @if ($errors->sandi->has('sandi_lama'))<div class="err">{{ $errors->sandi->first('sandi_lama') }}</div>@endif</div>
          <div class="f"><label for="sandi_baru">Kata sandi baru</label>
            <div class="pw"><input type="password" id="sandi_baru" name="sandi_baru" autocomplete="new-password" placeholder="Minimal 8 karakter" class="{{ $errors->sandi->has('sandi_baru') ? 'bad' : '' }}"><button type="button" data-lihat="sandi_baru" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div>
            @if ($errors->sandi->has('sandi_baru'))<div class="err">{{ $errors->sandi->first('sandi_baru') }}</div>@endif</div>
          <div class="f"><label for="sandi_baru_confirmation">Konfirmasi kata sandi</label>
            <div class="pw"><input type="password" id="sandi_baru_confirmation" name="sandi_baru_confirmation" autocomplete="new-password"><button type="button" data-lihat="sandi_baru_confirmation" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div></div>
        </div>
        <div class="fa"><button class="btn">Perbarui Kata Sandi</button></div>
      </form>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-lihat]').forEach(b => b.addEventListener('click', () => {
  const i = document.getElementById(b.dataset.lihat); i.type = i.type === 'password' ? 'text' : 'password';
}));
</script>
@endpush
