@extends('layouts.app')
@section('title', ($a ? 'Ubah' : 'Tambah') . ' Alamat - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('akun') }}">Akun Saya</a> &rsaquo; <a href="{{ route('akun.alamat') }}">Daftar Alamat</a> &rsaquo; <span>{{ $a ? 'Ubah' : 'Tambah' }}</span></nav>
  <div class="ak">
    @include('pelanggan._akun_menu')
    <form class="panel" method="post" action="{{ $a ? route('akun.alamat.perbarui', $a->id_alamat) : route('akun.alamat.simpan') }}" novalidate>
      @csrf
      @if ($a) @method('PUT') @endif
      <h2>{{ $a ? 'Ubah Alamat' : 'Tambah Alamat Baru' }}</h2>
      <div class="f" style="margin-top:14px"><label for="label">Label alamat</label>
        <input type="text" id="label" name="label" value="{{ old('label', $a->label ?? '') }}" maxlength="40" placeholder="Contoh: Rumah, Kantor" class="{{ $errors->has('label') ? 'bad' : '' }}">
        @error('label')<div class="err">{{ $message }}</div>@enderror</div>
      <div class="fg2">
        <div class="f"><label for="nama_penerima">Nama penerima</label>
          <input type="text" id="nama_penerima" name="nama_penerima" value="{{ old('nama_penerima', $a->nama_penerima ?? auth()->user()->nama) }}" maxlength="100" class="{{ $errors->has('nama_penerima') ? 'bad' : '' }}">
          @error('nama_penerima')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="f"><label for="no_telp">Nomor telepon</label>
          <input type="text" id="no_telp" name="no_telp" value="{{ old('no_telp', $a->no_telp ?? auth()->user()->no_telp) }}" inputmode="numeric" class="{{ $errors->has('no_telp') ? 'bad' : '' }}">
          @error('no_telp')<div class="err">{{ $message }}</div>@enderror</div>
      </div>
      <div class="f"><label for="alamat">Alamat lengkap</label>
        <textarea id="alamat" name="alamat" rows="3" maxlength="255" placeholder="Jalan, nomor rumah, RT/RW, desa/kelurahan" class="{{ $errors->has('alamat') ? 'bad' : '' }}">{{ old('alamat', $a->alamat ?? '') }}</textarea>
        @error('alamat')<div class="err">{{ $message }}</div>@enderror</div>
      <div class="fg2">
        <div class="f"><label for="kota">Kota / Kabupaten</label>
          <input type="text" id="kota" name="kota" value="{{ old('kota', $a->kota ?? '') }}" maxlength="100" class="{{ $errors->has('kota') ? 'bad' : '' }}">
          @error('kota')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="f"><label for="kode_pos">Kode pos</label>
          <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $a->kode_pos ?? '') }}" inputmode="numeric" maxlength="5" class="{{ $errors->has('kode_pos') ? 'bad' : '' }}">
          @error('kode_pos')<div class="err">{{ $message }}</div>@enderror</div>
      </div>
      @unless ($a && $a->utama)
        <label class="ck"><input type="checkbox" name="utama" value="1" @checked(old('utama'))> Jadikan alamat utama</label>
      @endunless
      <div class="fa"><a class="btn out" href="{{ route('akun.alamat') }}">Batal</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan Alamat</button></div>
    </form>
  </div>
</div>
@endsection
