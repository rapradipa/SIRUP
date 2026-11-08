@extends('layouts.app')
@section('title', 'Riwayat Transaksi - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('akun') }}">Akun Saya</a> &rsaquo; <span>Riwayat Transaksi</span></nav>
  <div class="ak">
    @include('pelanggan._akun_menu')
    <div style="min-width:0">
  <h1 class="pt">Riwayat Transaksi</h1>

  <section class="stats s3" aria-label="Ringkasan">
    <div class="panel stat"><div class="h">Total Transaksi <span class="ic"><x-ikon n="cart" /></span></div><b>{{ str_pad($ringkas['total'], 2, '0', STR_PAD_LEFT) }}</b></div>
    <div class="panel stat"><div class="h">Menunggu Pembayaran <span class="ic a"><x-ikon n="file" /></span></div><b>{{ str_pad($ringkas['menunggu'], 2, '0', STR_PAD_LEFT) }}</b></div>
    <div class="panel stat"><div class="h">Sedang Dikirim <span class="ic"><x-ikon n="truck" /></span></div><b>{{ str_pad($ringkas['dikirim'], 2, '0', STR_PAD_LEFT) }}</b></div>
  </section>

  <div class="panel" style="margin-top:20px">
    <div class="chips">
      @foreach ($filter as $k => $l)
        <a class="chip2 {{ $status === $k ? 'on' : '' }}" href="{{ route('pesanan', array_filter(['status' => $k === 'semua' ? null : $k, 'q' => $q ?: null])) }}">{{ $l }}</a>
      @endforeach
    </div>
    <form class="fbar" method="get" action="{{ route('pesanan') }}" role="search">
      @if ($status !== 'semua')<input type="hidden" name="status" value="{{ $status }}">@endif
      <input type="search" name="q" value="{{ $q }}" maxlength="100" placeholder="Cari No. Transaksi atau nama produk" aria-label="Cari transaksi">
      <button class="btn sm">Cari</button>
    </form>

    @if ($pesanan->count())
      <div class="tw"><table>
        <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Item</th><th>Total</th><th>Pembayaran</th><th>Pengiriman</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach ($pesanan as $p)
          @php
            $lunas = strtolower($p->status_pembayaran ?? '') === 'lunas';
            $nama = $item[$p->id_transaksi] ?? collect();
            $sg = strtolower($p->status_pengiriman ?? '');
          @endphp
          <tr>
            <td>TRX-{{ str_pad($p->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($p->tanggal)->format('d/m/Y') }}</td>
            <td>{{ $nama->first()->nama_produk ?? '-' }}@if ($nama->count() > 1) +{{ $nama->count() - 1 }} lainnya @endif</td>
            <td>{{ $rp($p->total) }}</td>
            <td><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></td>
            <td>@if ($sg !== '')<span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span>@else - @endif</td>
            <td class="aks">
              @if (! $lunas)
                <a class="ib2 pri" href="{{ route('pembayaran', $p->id_transaksi) }}" title="Bayar" aria-label="Bayar"><x-ikon n="card" :s="16" /></a>
              @else
                <a class="ib2" href="{{ route('pesanan.show', $p->id_transaksi) }}" title="Detail" aria-label="Detail"><x-ikon n="eye" :s="16" /></a>
                <a class="ib2" href="{{ route('pesanan.invoice', $p->id_transaksi) }}" target="_blank" rel="noopener" title="Invoice" aria-label="Invoice"><x-ikon n="file" :s="16" /></a>
                @if ($sg === '')
                  <a class="ib2 pri" href="{{ route('pembayaran', $p->id_transaksi) }}" title="Atur pengiriman" aria-label="Atur pengiriman"><x-ikon n="pin" :s="16" /></a>
                @elseif (($p->jenis ?? 'kirim') !== 'ambil')
                  <a class="ib2" href="{{ route('pesanan.lacak', $p->id_transaksi) }}" title="Lacak pengiriman" aria-label="Lacak pengiriman"><x-ikon n="truck" :s="16" /></a>
                @endif
              @endif
            </td>
          </tr>
        @endforeach
        </tbody>
      </table></div>
      {{ $pesanan->links('vendor.pagination.raden') }}
    @else
      <p class="empty">Tidak ada transaksi yang cocok. <a class="lnk" href="{{ route('katalog') }}">Mulai belanja</a></p>
    @endif
  </div>
    </div>
  </div>
</div>
@endsection
