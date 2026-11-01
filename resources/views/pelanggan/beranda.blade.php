@extends('layouts.app')
@section('title', 'Beranda - Raden Group')
@section('strip', '1')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <section class="hero">
    <span class="pill">Toko Perabot Rumah Tangga</span>
    <h1>Lengkapi Rumah Anda, Belanja dari Mana Saja</h1>
    <p>Cari barang dengan teks atau foto lewat kamera cerdas.</p>
    <a class="go" href="{{ route('katalog') }}">Belanja Sekarang</a>
    <span class="disc" aria-hidden="true"></span>
    <img src="{{ asset('img/ilustrasi/lemari.png') }}" alt="">
  </section>

  <div class="sec"><h2>Kategori Pilihan</h2><a href="{{ route('katalog') }}">Lihat semua</a></div>
  <section class="cats" aria-label="Kategori">
    @foreach ($kategori as $k)
      <a class="cat" href="{{ route('katalog', ['kategori' => [$k->id_kategori]]) }}"><span class="ci"><x-ikon :n="\App\Support\IkonKategori::untuk($k->id_kategori)" :s="20" /></span>{{ $k->nama_kategori }}</a>
    @endforeach
  </section>

  <div class="sec"><h2>Rekomendasi untukmu</h2><a href="{{ route('katalog') }}">Lihat semua</a></div>
  <section class="grid4" aria-label="Rekomendasi produk">
    @forelse ($produk as $p)
      @include('pelanggan._kartu', ['p' => $p])
    @empty
      <p class="empty">Belum ada produk tersedia.</p>
    @endforelse
  </section>
</div>
@endsection
