@extends('layouts.app')
@section('title', 'Detail Transaksi - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $kode = 'TRX-' . str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT);
  $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas';
  $sg = strtolower($t->status_pengiriman ?? '');
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
  $metode = ['tunai' => 'Tunai di Toko', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS'];
  $bankLabel = ['bri' => 'BRI', 'mandiri' => 'Mandiri'];
  $namaMetode = ($metode[$t->metode] ?? ($t->metode ?: '-')) . ($t->metode === 'transfer' && ! empty($t->bank) ? ' (' . ($bankLabel[$t->bank] ?? $t->bank) . ')' : '');
  $adaDiskon = $items->contains(fn ($i) => $i->diskon > 0);
  $daftar = $items->sum(fn ($i) => $i->harga * $i->jumlah);
@endphp
<div class="wrap">
  <nav class="crumb noprint" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('pesanan') }}">Pesanan Saya</a> &rsaquo; <a href="{{ route('pesanan') }}">Riwayat Transaksi</a> &rsaquo; <span>{{ $kode }}</span></nav>
  <div class="kh">
    <h1 class="pt" style="margin:0">Detail Transaksi</h1>
    <div class="acts noprint" style="margin:0;display:flex;gap:10px">
      <a class="btn out" href="{{ route('pesanan.invoice', $t->id_transaksi) }}" target="_blank" rel="noopener">Cetak Invoice</a>
      @if ($sg !== '' && ($t->jenis ?? 'kirim') !== 'ambil')
        <a class="btn" href="{{ route('pesanan.lacak', $t->id_transaksi) }}"><x-ikon n="truck" :s="16" /> Lacak Pengiriman</a>
      @endif
    </div>
  </div>

  <div class="cg">
    <section class="panel">
      <div class="rw"><h2>Invoice</h2><b style="color:var(--blue)">{{ $kode }}</b></div>
      <div class="meta">
        <div><small>No. Transaksi</small><b>{{ $kode }}</b></div>
        <div><small>Tanggal</small><b>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</b></div>
        <div><small>Pembayaran</small><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></div>
        <div><small>Pengiriman</small>@if ($sg !== '')<span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span>@else<b>-</b>@endif</div>
      </div>
      <div class="tw"><table>
        <thead><tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
        <tbody>
        @foreach ($items as $i)
          <tr>
            <td><div style="display:flex;align-items:center;gap:10px"><span class="th mini">
              @if ($i->gambar)<img src="{{ asset('uploads/produk/' . $i->gambar) }}" alt="">@else<x-ikon :n="\App\Support\IkonKategori::untuk($i->id_kategori)" :s="18" />@endif
            </span>{{ $i->nama_produk }}</div></td>
            <td>{{ $rp($i->subtotal / $i->jumlah) }}</td><td>{{ $i->jumlah }}</td><td>{{ $rp($i->subtotal) }}</td>
          </tr>
        @endforeach
        </tbody>
      </table></div>
      <div class="tot">
        @if ($adaDiskon)
          <div class="rw"><span>Subtotal</span><b>{{ $rp($daftar) }}</b></div>
          <div class="rw"><span>Diskon</span><b>- {{ $rp(max(0, $daftar - $t->total)) }}</b></div>
        @endif
        <div class="rw tt"><span>Total</span><b>{{ $rp($t->total) }}</b></div>
      </div>
    </section>

    <div style="display:grid;gap:20px">
      <aside class="panel sm">
        <div class="rw"><h2>Pembayaran</h2><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></div>
        <div class="rw"><span>Metode</span><b>{{ $namaMetode }}</b></div>
        <div class="rw"><span>Jumlah {{ $lunas ? 'dibayar' : 'tagihan' }}</span><b>{{ $rp($t->dibayar ?? $t->total) }}</b></div>
        <div class="rw"><span>Tanggal bayar</span><b>{{ $t->dibayar_pada ? \Illuminate\Support\Carbon::parse($t->dibayar_pada)->format('d/m/Y') : '-' }}</b></div>
        @if (! empty($t->bukti))
          <p class="mu noprint" style="margin-top:6px"><a class="lnk" href="{{ asset('uploads/bukti/' . $t->bukti) }}" target="_blank" rel="noopener">Lihat bukti pembayaran yang dikirim</a></p>
        @endif
        @unless ($lunas)
          <a class="btn full noprint" href="{{ route('pembayaran', $t->id_transaksi) }}">Bayar Sekarang</a>
        @endunless
      </aside>
      <aside class="panel sm">
        <div class="rw"><h2>Pengiriman</h2>@if ($sg !== '')<span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span>@endif</div>
        @if ($sg === '')
          <p class="mu" style="margin:0">{{ $lunas ? 'Belum ada pilihan pengiriman.' : 'Tersedia setelah pembayaran lunas.' }}</p>
          @if ($lunas)<a class="btn out full noprint" href="{{ route('pembayaran', $t->id_transaksi) }}">Atur Pengiriman</a>@endif
        @else
          <div class="note"><small>Alamat tujuan</small><br>
            @if (($t->jenis ?? 'kirim') === 'ambil')<b>Ambil di toko Raden Group</b>
            @else<b>{{ $t->nama_penerima }} | {{ $t->no_telp_penerima }}</b><br>{{ $t->alamat_pengiriman }}@endif</div>
          <div class="rw"><span>No. Resi</span><b>{{ $t->no_resi ?: '-' }}</b></div>
          @if (($t->jenis ?? 'kirim') !== 'ambil')
            <a class="btn out full noprint" href="{{ route('pesanan.lacak', $t->id_transaksi) }}"><x-ikon n="truck" :s="16" /> Lacak Pengiriman</a>
          @endif
        @endif
      </aside>
    </div>
  </div>
</div>
@endsection
