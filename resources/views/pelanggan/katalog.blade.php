@extends('layouts.app')
@section('title', $judul . ' - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('katalog') }}">Katalog</a>@if (count($f['kat']) === 1) &rsaquo; <span>{{ $judul }}</span>@endif</nav>
  <div class="kg">
    <aside>
      <form id="fl" class="panel fp" method="get" action="{{ route('katalog') }}">
        <details class="fdet" open>
        <summary class="fsum"><h2>Filter Produk</h2><span class="fchev" aria-hidden="true">&#9662;</span></summary>
        @if ($f['q'] !== '')<input type="hidden" name="q" value="{{ $f['q'] }}">@endif
        <h3>Kategori</h3>
        @foreach ($kategori as $k)
          <label class="ck"><input type="checkbox" name="kategori[]" value="{{ $k->id_kategori }}" @checked(in_array($k->id_kategori, $f['kat']))> {{ $k->nama_kategori }}</label>
        @endforeach
        <h3>Rentang Harga</h3>
        <div class="rg">
          <input type="number" name="min" min="0" step="1000" placeholder="Rp Min" value="{{ $f['min'] }}" aria-label="Harga minimum">
          <input type="number" name="maks" min="0" step="1000" placeholder="Rp Maks" value="{{ $f['maks'] }}" aria-label="Harga maksimum">
        </div>
        <h3>Ketersediaan</h3>
        <label class="ck"><input type="checkbox" name="tersedia" value="1" @checked($f['ada'])> Stok tersedia</label>
        <button class="btn full" style="margin-top:16px;height:44px">Terapkan Filter</button>
        </details>
      </form>
    </aside>

    <section>
      <div class="kh">
        <div><h1>{{ $judul }}</h1><span class="mu">Menampilkan {{ $produk->total() }} produk</span></div>
        <label class="srt">Urutan:
          <select name="urut" form="fl" onchange="this.form.submit()" aria-label="Urutkan produk">
            @foreach ($urutan as $v => $t)
              <option value="{{ $v }}" @selected($f['urut'] === $v)>{{ $t }}</option>
            @endforeach
          </select>
        </label>
      </div>

      @if ($produk->count())
        <div class="grid3">
          @foreach ($produk as $p)
            @include('pelanggan._kartu', ['p' => $p])
          @endforeach
        </div>
        {{ $produk->links('vendor.pagination.raden') }}
      @else
        <div class="panel empty-box">Tidak ada produk yang cocok dengan filter ini. <a href="{{ route('katalog') }}">Hapus semua filter</a></div>
      @endif
    </section>
  </div>
</div>
@endsection
@push('scripts')
<script>(function(){var d=document.querySelector('.fdet');if(d&&window.matchMedia('(max-width:1000px)').matches)d.open=false;})();</script>
@endpush
