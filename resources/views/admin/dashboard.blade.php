@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $dua = fn ($n) => str_pad($n, 2, '0', STR_PAD_LEFT);
  $warna = ['lunas' => 'g', 'selesai' => 'g', 'dikirim' => 'b', 'diproses' => 'b', 'menunggu' => 'y'];
  $lencana = fn ($s) => '<span class="bd ' . ($warna[strtolower($s ?? 'menunggu')] ?? 'b') . '">' . e(ucfirst($s ?? 'Menunggu')) . '</span>';
  $puncak = $grafik->sum('nilai') > 0 ? $grafik->search(fn ($g) => $g['nilai'] === $grafik->max('nilai')) : -1;
  $ilu = [1 => 'lemari', 4 => 'meja-rias', 5 => 'kulkas', 6 => 'kasur'];
@endphp

<section class="stats" aria-label="Ringkasan hari ini">
  <div class="panel stat"><div class="h">Penjualan Hari Ini <span class="ic"><x-ikon n="cart" /></span></div><b>{{ $rp($penjualanHariIni) }}</b></div>
  <div class="panel stat"><div class="h">Jumlah Transaksi <span class="ic"><x-ikon n="file" /></span></div><b>{{ $dua($transaksiHariIni) }}</b></div>
  <div class="panel stat"><div class="h">Pengiriman Aktif <span class="ic"><x-ikon n="truck" /></span></div><b>{{ $dua($pengirimanAktif) }}</b></div>
  <div class="panel stat"><div class="h">Stok Menipis <span class="ic a"><x-ikon n="box" /></span></div><b>{{ $dua($jumlahMenipis) }}</b></div>
</section>

<section class="two">
  <div class="panel">
    <h2>Laporan Penjualan Harian</h2>
    <p class="sub">Total pembayaran lunas per hari, minggu ini</p>
    <div class="chart" role="img" aria-label="Grafik penjualan minggu ini">
      @foreach ($grafik as $i => $g)
        <div title="{{ $rp($g['nilai']) }}"><i class="{{ $i === $puncak ? 'hi' : '' }}" style="height:{{ max($g['nilai'] / $maks * 85, 12) }}%"></i>{{ $g['label'] }}</div>
      @endforeach
    </div>
  </div>
  <div class="panel">
    <h2>Stok Menipis</h2>
    <p class="sub">Perlu segera restock</p>
    <ul class="lowl">
      @forelse ($menipis as $p)
        <li><span class="th"><img src="{{ asset('img/ilustrasi/' . ($ilu[$p->id_kategori] ?? 'lemari') . '.png') }}" alt=""></span><span class="nm">{{ $p->nama_produk }}</span><span class="bd y">Sisa {{ $dua($p->stok) }}</span></li>
      @empty
        <li class="empty">Semua stok aman.</li>
      @endforelse
    </ul>
  </div>
</section>

<section class="panel">
  <h2>Transaksi Terbaru</h2>
  <p class="sub">Lima transaksi terakhir</p>
  <div class="tw"><table>
    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Pembayaran</th><th>Pengiriman</th></tr></thead>
    <tbody>
    @forelse ($terbaru as $t)
      <tr>
        <td>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
        <td>{{ $t->nama }}</td><td>{{ $rp($t->total) }}</td>
        <td>{!! $lencana($t->status_pembayaran) !!}</td><td>{!! $lencana($t->status_pengiriman) !!}</td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Belum ada transaksi.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</section>
@endsection
