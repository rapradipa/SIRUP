@extends('layouts.app')
@section('title', 'Profil Saya - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $ubin = [['menunggu', 'Menunggu Pembayaran', 'clock', true, 'menunggu'], ['diproses', 'Diproses', 'box', false, null], ['dikirim', 'Dikirim', 'truck', false, 'dikirim'], ['selesai', 'Selesai', 'check', false, 'selesai']];
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <span>Akun Saya</span> &rsaquo; <span>Profil Pengguna</span></nav>
  <div class="ak">
    @include('pelanggan._akun_menu')
    <div style="display:grid;gap:20px">
      <section class="panel pfh">
        <span class="av xl">{{ strtoupper(substr($p->nama, 0, 1)) }}</span>
        <div class="pfi"><h1>{{ $p->nama }}</h1><div class="mu">{{ $p->no_telp }}@if ($p->email) &middot; {{ $p->email }}@endif</div><span class="bd">Pelanggan</span></div>
        <a class="btn out" href="{{ route('akun.ubah') }}"><x-ikon n="edit" :s="16" /> Ubah Profil</a>
      </section>

      <section class="panel">
        <div class="rw" style="padding-top:0"><h2>Status Pesanan</h2><a class="lnk" href="{{ route('pesanan') }}">Lihat Riwayat</a></div>
        <div class="tiles">
          @foreach ($ubin as [$k, $l, $ik, $amber, $f])
            <a class="tile" href="{{ route('pesanan', $f ? ['status' => $f] : []) }}"><span class="ic {{ $amber ? 'a' : '' }}"><x-ikon :n="$ik" :s="18" /></span><b>{{ str_pad($status[$k], 2, '0', STR_PAD_LEFT) }}</b><small>{{ $l }}</small></a>
          @endforeach
        </div>
      </section>

      <section class="panel">
        <h2>Transaksi Terakhir</h2>
        @if ($terakhir)
          @php $sg = strtolower($terakhir->status_pengiriman ?? ''); $ok = strtolower($terakhir->status_pembayaran ?? '') === 'lunas'; @endphp
          <div class="last">
            <div class="ib">
              <b>TRX-{{ str_pad($terakhir->id_transaksi, 4, '0', STR_PAD_LEFT) }}</b>
              <div class="mu">{{ $item->first() }}@if ($item->count() > 1) +{{ $item->count() - 1 }} lainnya @endif | {{ \Illuminate\Support\Carbon::parse($terakhir->tanggal)->format('d/m/Y') }}</div>
              <div class="pr" style="font-size:16px">{{ $rp($terakhir->total) }}</div>
            </div>
            <span class="bd {{ $sg === 'selesai' || (! $sg && $ok) ? 'g' : ($sg ? 'b' : 'y') }}">{{ $sg ? ucfirst($sg) : ($ok ? 'Lunas' : 'Menunggu') }}</span>
            <a class="btn out" href="{{ route('pesanan.show', $terakhir->id_transaksi) }}">Lihat Detail</a>
          </div>
        @else
          <p class="empty">Belum ada transaksi. <a class="lnk" href="{{ route('katalog') }}">Mulai belanja</a></p>
        @endif
      </section>
    </div>
  </div>
</div>
@endsection
