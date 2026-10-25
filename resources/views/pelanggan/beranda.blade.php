@extends('layouts.app')
@section('title', 'Beranda - Raden Group')
@section('strip', '1')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $ilu = [1 => 'lemari', 4 => 'meja-rias', 5 => 'kulkas', 6 => 'kasur'];
  $ikon = [1 => 'armchair', 2 => 'pot', 3 => 'home', 4 => 'lamp', 5 => 'monitor', 6 => 'bed', 7 => 'drop', 8 => 'wrench'];
@endphp
<div class="wrap">
  <section class="hero">
    <span class="pill">Toko Perabot Rumah Tangga</span>
    <h1>Lengkapi Rumah Anda, Belanja dari Mana Saja</h1>
    <p>Cari barang dengan teks atau foto lewat kamera cerdas.</p>
    <span class="go soon" title="Katalog segera hadir">Belanja Sekarang</span>
    <span class="disc" aria-hidden="true"></span>
    <img src="{{ asset('img/ilustrasi/lemari.png') }}" alt="">
  </section>

  <div class="sec"><h2>Kategori Pilihan</h2><a class="soon" title="Segera hadir">Lihat semua</a></div>
  <section class="cats" aria-label="Kategori">
    @foreach ($kategori as $k)
      <div class="cat soon" title="Segera hadir"><span class="ci"><x-ikon :n="$ikon[$k->id_kategori] ?? 'tag'" :s="20" /></span>{{ $k->nama_kategori }}</div>
    @endforeach
  </section>

  <div class="sec"><h2>Rekomendasi untukmu</h2><a class="soon" title="Segera hadir">Lihat semua</a></div>
  <section class="grid4" aria-label="Rekomendasi produk">
    @forelse ($produk as $p)
      @php $akhir = $p->harga * (100 - $p->diskon) / 100; @endphp
      <article class="pc">
        <div class="ph"><img src="{{ asset('img/ilustrasi/' . ($ilu[$p->id_kategori] ?? 'lemari') . '.png') }}" alt="{{ $p->nama_produk }}"></div>
        <div class="cb">
          <div class="nm">{{ $p->nama_produk }}</div>
          <div class="pr">{{ $rp($akhir) }} @if ($p->diskon)<span class="dc">-{{ $p->diskon }}%</span>@endif</div>
          <div class="old">@if ($p->diskon){{ $rp($p->harga) }}@endif</div>
          <span class="bd g">Stok: {{ $p->stok }}</span>
          <button type="button" class="btn soon" title="Keranjang segera hadir"><x-ikon n="cart" :s="16" /> Tambah ke Keranjang</button>
        </div>
      </article>
    @empty
      <p class="empty">Belum ada produk tersedia.</p>
    @endforelse
  </section>
</div>
@endsection
