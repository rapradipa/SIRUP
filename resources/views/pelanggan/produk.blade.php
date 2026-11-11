@extends('layouts.app')
@section('title', $p->nama_produk . ' - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $akhir = $p->harga * (100 - $p->diskon) / 100;
  $habis = $p->stok <= 0;
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('katalog') }}">Katalog</a> &rsaquo; <a href="{{ route('katalog', ['kategori' => [$p->id_kategori]]) }}">{{ $p->nama_kategori }}</a> &rsaquo; <span>{{ $p->nama_produk }}</span></nav>
  <div class="dt">
    <div class="dimg">
      @if ($p->gambar)
        <img src="{{ asset('uploads/produk/' . $p->gambar) }}" alt="{{ $p->nama_produk }}">
      @else
        <span class="phi" role="img" aria-label="{{ $p->nama_kategori }}"><x-ikon :n="\App\Support\IkonKategori::untuk($p->id_kategori)" :s="120" /></span>
      @endif
    </div>
    <div class="di">
      <span class="bd {{ $habis ? 'r' : 'g' }}">{{ $habis ? 'Stok habis' : 'Stok: ' . $p->stok }}</span>
      <h1>{{ $p->nama_produk }}</h1>
      <div class="pr big">{{ $rp($akhir) }}
        @if ($p->diskon)
          <span class="dc">-{{ $p->diskon }}%</span> <span class="old">{{ $rp($p->harga) }}</span>
        @endif
      </div>
      <dl class="spec">
        <div><dt>Kategori</dt><dd>{{ $p->nama_kategori }}</dd></div>
        <div><dt>Supplier</dt><dd>{{ $p->nama_supplier ?? '-' }}</dd></div>
        <div><dt>Stok</dt><dd>{{ $p->stok }} unit</dd></div>
      </dl>
      <form method="post" action="{{ route('keranjang.tambah') }}">
        @csrf
        <input type="hidden" name="id_produk" value="{{ $p->id_produk }}">
        @unless ($habis)
          <div class="qr"><span>Jumlah</span>
            <div class="qty">
              <button type="button" data-d="-1" aria-label="Kurangi">&minus;</button>
              <input type="text" id="qty" name="jumlah" value="1" inputmode="numeric" data-max="{{ $p->stok }}" aria-label="Jumlah">
              <button type="button" data-d="1" aria-label="Tambah">+</button>
            </div>
          </div>
        @endunless
        <div class="acts">
          <button name="aksi" value="tambah" class="btn out" data-ajax @disabled($habis)><x-ikon n="cart" :s="16" /> Tambah ke Keranjang</button>
          <button name="aksi" value="beli" class="btn" @disabled($habis)>Beli Sekarang</button>
        </div>
      </form>
      <div class="dsc"><b>Deskripsi Produk</b><p>{{ $p->deskripsi ?: 'Belum ada deskripsi untuk produk ini.' }}</p></div>
    </div>
  </div>
</div>
@endsection
@push('scripts')
<script>
const q = document.getElementById('qty');
if (q) document.querySelectorAll('.qty button').forEach(b => b.addEventListener('click', () => {
  const max = +q.dataset.max; q.value = Math.min(max, Math.max(1, (parseInt(q.value) || 1) + +b.dataset.d));
}));
if (q) q.addEventListener('change', () => { const max = +q.dataset.max; q.value = Math.min(max, Math.max(1, parseInt(q.value) || 1)); });
</script>
@endpush
