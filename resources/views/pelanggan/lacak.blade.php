@extends('layouts.app')
@section('title', 'Lacak Pengiriman - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('pesanan') }}">Pesanan Saya</a> &rsaquo; <span>Lacak Pengiriman</span></nav>
  <div class="kh">
    <h1 class="pt" style="margin:0">Lacak Pengiriman</h1>
    <form class="fbar" style="margin:0;min-width:min(340px,100%)" method="get" action="{{ route('lacak') }}" role="search">
      <input type="search" name="q" value="{{ $q }}" maxlength="60" placeholder="Masukkan No. Transaksi atau No. Resi" aria-label="No. Transaksi atau No. Resi">
      <button class="btn sm">Lacak</button>
    </form>
  </div>

  @if (! $t)
    <div class="panel empty-box">
      {{ $dicari ? 'Pesanan tidak ditemukan. Periksa kembali nomor transaksi atau nomor resi Anda.' : 'Masukkan nomor transaksi (contoh: TRX-0001) atau nomor resi untuk melihat status pengiriman.' }}
    </div>
  @elseif (! $g)
    <div class="panel empty-box">Transaksi TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }} belum memiliki data pengiriman.
      <a href="{{ route('pembayaran', $t->id_transaksi) }}">Atur pengiriman</a></div>
  @else
    @php
      $sg = strtolower($g->status_pengiriman);
      $ambil = $g->jenis === 'ambil';
    @endphp
    <section class="panel">
      <div class="meta">
        <div><small>No. Transaksi</small><b>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</b></div>
        <div><small>No. Resi</small><b>{{ $g->no_resi ?: '-' }}</b></div>
        <div><small>Kurir</small><b>{{ $g->kurir ?: '-' }}</b></div>
        <div><small>Estimasi Tiba</small><b>{{ $g->estimasi_tiba ? \Illuminate\Support\Carbon::parse($g->estimasi_tiba)->format('d/m/Y') : '-' }}</b></div>
        <div style="margin-left:auto"><span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span></div>
      </div>
      <div class="stp" style="--n:{{ count($langkah) }}">
        @foreach ($langkah as [$nama, $on])
          <div class="{{ $on ? 'on' : '' }}">{{ $nama }}</div>
        @endforeach
      </div>
    </section>

    <div class="cg rev">
      <section class="panel">
        <h2>Riwayat Perjalanan</h2>
        <p class="sub">Pembaruan terbaru ada di bagian atas</p>
        <ol class="tl">
          @foreach ($riwayat as $n => [$judul, $ket, $waktu, $jam])
            <li class="{{ $n === 0 ? 'now' : '' }}">
              <i>{!! $n === 0 ? '' : '&#10003;' !!}</i>
              <b>{{ $judul }}</b><small>{{ $ket }}</small>
              @if ($waktu)<time>{{ $waktu->format($jam ? 'd/m/Y H:i' : 'd/m/Y') }}</time>@endif
            </li>
          @endforeach
        </ol>
      </section>
      <aside class="panel sm">
        <h2>{{ $ambil ? 'Pengambilan' : 'Alamat Tujuan' }}</h2>
        <div class="note">
          @if ($ambil)<b>Ambil di toko Raden Group</b><br>Jl. Raya Caruban-Ngawi, Desa Karangjati
          @else<b>{{ $g->nama_penerima }} | {{ $g->no_telp_penerima }}</b><br>{{ $g->alamat_pengiriman }}@endif
        </div>
        @foreach ($items as $i)
          <div class="ci slim">
            <span class="th">
              @if ($i->gambar)<img src="{{ asset('uploads/produk/' . $i->gambar) }}" alt="">@else<x-ikon :n="\App\Support\IkonKategori::untuk($i->id_kategori)" :s="20" />@endif
            </span>
            <div class="ib"><b>{{ $i->nama_produk }}</b><small class="mu" style="display:block">Jumlah: {{ $i->jumlah }}</small></div>
          </div>
        @endforeach
        @if ($sg === 'dikirim')
          <form method="post" action="{{ route('pesanan.terima', $t->id_transaksi) }}" data-confirm="Konfirmasi bahwa pesanan sudah Anda terima?">
            @csrf
            <button class="btn full">Pesanan Diterima</button>
          </form>
        @endif
      </aside>
    </div>
  @endif
</div>
@endsection
