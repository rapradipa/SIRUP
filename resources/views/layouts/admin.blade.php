<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') - Panel Admin Raden Group</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php $u = auth()->user(); @endphp
<div class="adm">
  <aside class="side">
    <div class="lg"><img src="{{ asset('img/logo.png') }}" alt="Raden Group"><small>Panel Admin</small></div>
    <nav aria-label="Menu admin">
      @php
        $nav = [
          ['grid', 'Dashboard', 'admin.dashboard', ['admin.dashboard'], false],
          ['box', 'Produk & Kategori', 'admin.produk', ['admin.produk*', 'admin.kategori*'], true],
          ['download', 'Stok Masuk', 'admin.stok', ['admin.stok*'], false],
          ['store', 'Supplier', 'admin.supplier', ['admin.supplier*'], true],
          ['cart', 'Transaksi & Pembayaran', 'admin.transaksi', ['admin.transaksi*'], false],
          ['truck', 'Pengiriman', 'admin.pengiriman', ['admin.pengiriman*'], false],
          ['file', 'Laporan Penjualan', 'admin.laporan', ['admin.laporan*'], true],
          ['user', 'Pengguna', 'admin.pengguna', ['admin.pengguna*'], true],
        ];
      @endphp
      @foreach ($nav as [$ik, $m, $rt, $pola, $khusus])
        @continue($khusus && $u->role !== 'admin')
        @if ($rt)
          <a href="{{ route($rt) }}" class="{{ request()->routeIs(...$pola) ? 'on' : '' }}" {{ request()->routeIs(...$pola) ? 'aria-current=page' : '' }}><x-ikon :n="$ik" /> {{ $m }}</a>
        @else
          <a class="soon" aria-disabled="true" title="Segera hadir"><x-ikon :n="$ik" /> {{ $m }}</a>
        @endif
      @endforeach
    </nav>
  </aside>
  <div class="main">
    <div class="bar">
      <h1>@yield('title')</h1>
      <div class="r">
        <div class="find soon" title="Segera hadir"><x-ikon n="search" :s="16" /> Cari data...</div>
        <details class="me">
          <summary><span class="av">{{ strtoupper(substr($u->nama, 0, 1)) }}</span><span><b>{{ $u->nama }}</b><small>Karyawan / {{ $u->role === 'admin' ? 'Admin' : 'Kasir' }}</small></span></summary>
          <div class="menu"><form method="post" action="{{ route('logout') }}">@csrf<button>Keluar</button></form></div>
        </details>
      </div>
    </div>
    <div class="body">@include('partials.flash')@yield('content')</div>
  </div>
</div>
@stack('scripts')
</body>
</html>
