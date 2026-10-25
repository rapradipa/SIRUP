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
  <div class="sr soon" title="Segera hadir" aria-disabled="true">
    <x-ikon n="search" :s="18" />
    <span class="t">Cari lemari, kulkas, kasur, dan perabotan lainnya</span>
    <span class="cam"><x-ikon n="camera" :s="16" /> Cari dengan Kamera</span>
  </div>
  <span class="hb navy soon hide" title="Segera hadir">Cari</span>
  <span class="hb soon hide" title="Segera hadir"><x-ikon n="cart" :s="18" /> Keranjang</span>
  @if ($u)
    <span class="hb soon hide" title="Segera hadir"><x-ikon n="phone" :s="18" /> Kontak</span>
    <span class="chip"><span class="av">{{ strtoupper(substr($u->nama, 0, 1)) }}</span>{{ $u->nama }}</span>
    <form method="post" action="{{ route('logout') }}">@csrf<button class="hb">Keluar</button></form>
  @else
    <a class="hb white" href="{{ route('login') }}">Masuk</a>
  @endif
</div></header>
<main class="page @yield('page_class')">@yield('content')</main>
<footer class="foot"><div class="in"><b>Raden Group</b><span>Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur</span></div></footer>
@stack('scripts')
</body>
</html>
