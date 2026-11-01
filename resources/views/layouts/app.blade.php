<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'Raden Group')</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php $u = auth()->user(); @endphp
@hasSection('strip')
<div class="strip"><div class="in"><span>Belanja perabotan rumah tangga langsung dari toko Raden Group</span><span class="soon" title="Segera hadir">Bantuan</span></div></div>
@endif
<header class="top"><div class="in">
  <a class="logo" href="/"><img src="{{ asset('img/logo.png') }}" alt="Raden Group"></a>
  <form id="cari" class="sr" method="get" action="{{ route('katalog') }}" role="search">
    <x-ikon n="search" :s="18" />
    <input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Cari lemari, kulkas, kasur, dan perabotan lainnya" aria-label="Cari produk">
    <span class="cam soon" title="Segera hadir"><x-ikon n="camera" :s="16" /> Cari dengan Kamera</span>
  </form>
  <button form="cari" class="hb navy hide">Cari</button>
  @php $nk = ($u && $u->id_pelanggan) ? (int) \Illuminate\Support\Facades\DB::table('keranjang')->where('id_pelanggan', $u->id_pelanggan)->sum('jumlah') : 0; @endphp
  <a class="hb hide" href="{{ route('keranjang') }}"><x-ikon n="cart" :s="18" /> Keranjang @if ($nk)<span class="cnt">{{ $nk }}</span>@endif</a>
  @if ($u)
    <span class="hb soon hide" title="Segera hadir"><x-ikon n="phone" :s="18" /> Kontak</span>
    <a class="chip" href="{{ $u->isStaf() ? route('admin.dashboard') : route('pesanan') }}" title="{{ $u->isStaf() ? 'Dashboard admin' : 'Pesanan saya' }}"><span class="av">{{ strtoupper(substr($u->nama, 0, 1)) }}</span>{{ $u->nama }}</a>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="hb">Keluar</button></form>
  @else
    <a class="hb white" href="{{ route('login') }}">Masuk</a>
  @endif
</div></header>
<main class="page @yield('page_class')">@include('partials.flash')@yield('content')</main>
<footer class="foot"><div class="in"><b>Raden Group</b><span>Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur</span></div></footer>
@stack('scripts')
</body>
</html>
