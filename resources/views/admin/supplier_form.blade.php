@extends('layouts.admin')
@section('title', $s ? 'Ubah Supplier' : 'Tambah Supplier')
@section('content')
<form class="panel" style="max-width:620px" method="post" action="{{ $s ? route('admin.supplier.perbarui', $s->id_supplier) : route('admin.supplier.simpan') }}">
  @csrf
  @if ($s) @method('PUT') @endif
  <p class="crumb"><a href="{{ route('admin.supplier') }}">Supplier</a> &rsaquo; <span>{{ $s ? 'Ubah' : 'Tambah' }}</span></p>
  <h2>{{ $s ? 'Ubah Data Supplier' : 'Tambah Supplier Baru' }}</h2>
  <div class="f" style="margin-top:14px"><label for="nama_supplier">Nama supplier</label>
    <input type="text" id="nama_supplier" name="nama_supplier" value="{{ old('nama_supplier', $s->nama_supplier ?? '') }}" maxlength="100" required class="{{ $errors->has('nama_supplier') ? 'bad' : '' }}">
    @error('nama_supplier')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="f"><label for="alamat">Alamat</label>
    <input type="text" id="alamat" name="alamat" value="{{ old('alamat', $s->alamat ?? '') }}" maxlength="255" class="{{ $errors->has('alamat') ? 'bad' : '' }}">
    @error('alamat')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="f"><label for="no_telpon">No. telepon</label>
    <input type="text" id="no_telpon" name="no_telpon" value="{{ old('no_telpon', $s->no_telpon ?? '') }}" inputmode="numeric" placeholder="08xxxxxxxxxx" class="{{ $errors->has('no_telpon') ? 'bad' : '' }}">
    @error('no_telpon')<div class="err">{{ $message }}</div>@enderror</div>
  <div class="fa"><a class="btn out" href="{{ route('admin.supplier') }}">Batal</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan</button></div>
</form>
@endsection
