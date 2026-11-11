@extends('layouts.admin')
@section('title', 'Produk & Kategori')
@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<section class="panel">
  <div class="tbar">
    <form class="tf" method="get" action="{{ route('admin.produk') }}" role="search">
      <input type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Cari nama produk..." aria-label="Cari produk">
      <select name="kategori" onchange="this.form.submit()" aria-label="Filter kategori">
        <option value="0">Semua Kategori</option>
        @foreach ($kategori as $k)
          <option value="{{ $k->id_kategori }}" @selected($kat == $k->id_kategori)>{{ $k->nama_kategori }}</option>
        @endforeach
      </select>
      <button class="btn sm">Cari</button>
    </form>
    <div class="tb-r">
      <a class="btn out" href="{{ route('admin.kategori') }}">Kelola Kategori</a>
      <a class="btn" href="{{ route('admin.produk.baru', ['kembali' => request()->getRequestUri()]) }}"><x-ikon n="plus" :s="16" /> Tambah Produk</a>
    </div>
  </div>
  <h2>Daftar Produk</h2>
  <p class="sub">Kelola data produk, harga, dan stok</p>
  <div class="tw"><table>
    <thead><tr><th>Produk</th><th>Kategori</th><th>Supplier</th><th>Harga</th><th>Stok</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($produk as $p)
      @php [$kls, $lbl] = \App\Support\Stok::status($p->stok); @endphp
      <tr>
        <td><div class="pn"><span class="th mini">
          @if ($p->gambar)<img src="{{ asset('uploads/produk/' . $p->gambar) }}" alt="">@else<x-ikon :n="\App\Support\IkonKategori::untuk($p->id_kategori)" :s="18" />@endif
        </span><b>{{ $p->nama_produk }}</b></div></td>
        <td>{{ $p->nama_kategori ?? '-' }}</td>
        <td>{{ $p->nama_supplier ?? '-' }}</td>
        <td>{{ $rp($p->harga * (100 - $p->diskon) / 100) }}@if ($p->diskon) <span class="bd r">-{{ $p->diskon }}%</span>@endif</td>
        <td><span class="bd {{ $kls }}">{{ $lbl }}</span> <small class="mu">{{ $p->stok }}</small></td>
        <td class="aks">
          <a class="ib2" href="{{ route('admin.produk.ubah', [$p->id_produk, 'kembali' => request()->getRequestUri()]) }}" aria-label="Ubah {{ $p->nama_produk }}"><x-ikon n="edit" :s="15" /></a>
          <form method="post" action="{{ route('admin.produk.hapus', $p->id_produk) }}" data-confirm="{{ 'Hapus produk ' . $p->nama_produk . '?' }}" data-confirm-danger data-ajax data-refresh>
            @csrf @method('DELETE')
            <button class="ib2 red" aria-label="Hapus {{ $p->nama_produk }}"><x-ikon n="trash" :s="15" /></button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Tidak ada produk yang cocok.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $produk->links('vendor.pagination.raden') }}
</section>
@endsection
