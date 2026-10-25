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
      <a href="{{ route('admin.dashboard') }}" class="on" aria-current="page"><x-ikon n="grid" /> Dashboard</a>
      @foreach ([['box', 'Produk & Kategori'], ['download', 'Stok Masuk'], ['download', 'Supplier'], ['cart', 'Transaksi & Pembayaran'], ['truck', 'Pengiriman'], ['file', 'Laporan Penjualan']] as [$ik, $m])
        <a class="soon" aria-disabled="true" title="Segera hadir"><x-ikon :n="$ik" /> {{ $m }}</a>
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
    <div class="body">@yield('content')</div>
  </div>
</div>
</body>
</html>
