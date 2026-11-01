@extends('layouts.admin')
@section('title', 'Stok Masuk')
@section('content')
<div class="sg">
  <form class="panel" method="post" action="{{ route('admin.stok.simpan') }}">
    @csrf
    <h2>Input Stok Masuk</h2>
    <p class="sub">Stok produk bertambah otomatis</p>
    <div class="f"><label for="id_supplier">Supplier</label>
      <select id="id_supplier" name="id_supplier" required class="{{ $errors->has('id_supplier') ? 'bad' : '' }}">
        <option value="">Pilih supplier</option>
        @foreach ($supplier as $s)<option value="{{ $s->id_supplier }}" @selected((int) old('id_supplier') == $s->id_supplier)>{{ $s->nama_supplier }}</option>@endforeach
      </select>
      @error('id_supplier')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="id_produk">Produk</label>
      <select id="id_produk" name="id_produk" required class="{{ $errors->has('id_produk') ? 'bad' : '' }}">
        <option value="">Pilih produk</option>
        @foreach ($produk as $p)<option value="{{ $p->id_produk }}" @selected((int) old('id_produk') == $p->id_produk)>{{ $p->nama_produk }} (stok {{ $p->stok }}{{ $p->nama_supplier ? ', ' . $p->nama_supplier : '' }})</option>@endforeach
      </select>
      @error('id_produk')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="jumlah">Jumlah masuk</label>
      <input type="number" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" min="1" step="1" placeholder="0" required class="{{ $errors->has('jumlah') ? 'bad' : '' }}">
      @error('jumlah')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="tanggal">Tanggal masuk</label>
      <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $errors->has('tanggal') ? 'bad' : '' }}">
      @error('tanggal')<div class="err">{{ $message }}</div>@enderror</div>
    <button class="btn full">Simpan Stok Masuk</button>
  </form>

  <section class="panel">
    <h2>Riwayat Stok Masuk</h2>
    <p class="sub">Barang yang masuk dari supplier</p>
    <div class="tw"><table>
      <thead><tr><th>ID</th><th>Tanggal</th><th>Supplier</th><th>Produk</th><th>Jumlah</th></tr></thead>
      <tbody>
      @forelse ($riwayat as $m)
        <tr>
          <td>SM-{{ str_pad($m->id_stok_masuk, 4, '0', STR_PAD_LEFT) }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
          <td>{{ $m->nama_supplier }}</td><td>{{ $m->nama_produk }}</td><td>+{{ $m->jumlah }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty">Belum ada stok masuk.</td></tr>
      @endforelse
      </tbody>
    </table></div>
    {{ $riwayat->links('vendor.pagination.raden') }}
  </section>
</div>
@endsection
