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
<div class="strip"><div class="in"><span>Belanja perabotan rumah tangga langsung dari toko Raden Group</span></div></div>
@endif
<header class="top"><div class="in">
  <a class="logo" href="/"><img src="{{ asset('img/logo.png') }}" alt="Raden Group"></a>
  <form id="cari" class="sr" method="get" action="{{ route('katalog') }}" role="search">
    <x-ikon n="search" :s="18" />
    <input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Cari lemari, kulkas, kasur, dan perabotan lainnya" aria-label="Cari produk">
    <a class="cam" href="{{ route('cari.foto') }}"><x-ikon n="camera" :s="16" /> Cari dengan Foto</a>
  </form>
  <button form="cari" class="hb navy hide">Cari</button>
  @php $nk = ($u && $u->id_pelanggan) ? (int) \Illuminate\Support\Facades\DB::table('keranjang')->where('id_pelanggan', $u->id_pelanggan)->sum('jumlah') : 0; @endphp
  <a class="hb" id="navKeranjang" href="{{ route('keranjang') }}"><x-ikon n="cart" :s="18" /><span class="lb">Keranjang</span> @if ($nk)<span class="cnt">{{ $nk }}</span>@endif</a>
  @if ($u)
    <a class="hb" href="{{ route('kontak') }}"><x-ikon n="phone" :s="18" /><span class="lb">Kontak</span></a>
    <a class="chip" href="{{ $u->isStaf() ? route('admin.dashboard') : route('akun') }}" title="{{ $u->isStaf() ? 'Dashboard admin' : 'Akun saya' }}"><span class="av">{{ strtoupper(substr($u->nama, 0, 1)) }}</span><span class="lb">{{ $u->nama }}</span></a>
  @else
    <a class="hb white" href="{{ route('login') }}">Masuk</a>
  @endif
</div></header>
<main class="page @yield('page_class')">@include('partials.flash')@yield('content')</main>
<footer class="foot"><div class="in"><b>Raden Group</b><span>Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur &middot; <a href="{{ route('kontak') }}" style="text-decoration:underline">Kontak Kami</a></span></div></footer>
<div class="cf-bg" id="cfBg" hidden>
  <div class="cf-box" role="alertdialog" aria-modal="true" aria-labelledby="cfMsg">
    <p id="cfMsg"></p>
    <div class="cf-act">
      <button type="button" id="cfCancel">Batal</button>
      <button type="button" id="cfOk" class="cf-ok">OK</button>
    </div>
  </div>
</div>
@include('partials.ajax')
<script>
(function(){
  var bg=document.getElementById('cfBg'), msg=document.getElementById('cfMsg'), ok=document.getElementById('cfOk'), cancel=document.getElementById('cfCancel');
  var target=null;
  function tutup(){ bg.hidden=true; target=null; }
  document.addEventListener('submit', function(e){
    var f=e.target;
    if (f.tagName==='FORM' && f.hasAttribute('data-confirm')) {
      e.preventDefault();
      target=f;
      msg.textContent=f.getAttribute('data-confirm');
      ok.className='cf-ok' + (f.hasAttribute('data-confirm-danger') ? ' cf-danger' : '');
      bg.hidden=false;
      ok.focus();
    }
  });
  ok.addEventListener('click', function(){ var f=target; tutup(); if (!f) return; if (f.hasAttribute('data-ajax') && f.requestSubmit) { f.setAttribute('data-ok','1'); f.requestSubmit(); } else f.submit(); });
  cancel.addEventListener('click', tutup);
  bg.addEventListener('click', function(e){ if (e.target===bg) tutup(); });
  document.addEventListener('keydown', function(e){ if (e.key==='Escape' && !bg.hidden) tutup(); });
})();
</script>
@stack('scripts')
</body>
</html>
