@extends('layouts.admin')
@section('title', 'Laporan Penjualan')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $kosong = ['mode' => $mode];
  $puncak = collect($grafik)->sum('nilai') > 0 ? collect($grafik)->search(fn ($g) => $g['nilai'] === collect($grafik)->max('nilai')) : -1;
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<section class="panel">
  <div class="tbar" style="margin:0">
    <div class="lap-f">
      <div class="seg" role="tablist" aria-label="Periode">
        @foreach (['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'] as $k => $l)
          <a href="{{ route('admin.laporan', ['mode' => $k]) }}" class="{{ $mode === $k ? 'on' : '' }}">{{ $l }}</a>
        @endforeach
      </div>
      <form class="lap-f" method="get" action="{{ route('admin.laporan') }}">
        <input type="hidden" name="mode" value="{{ $mode }}">
        <input type="date" name="dari" value="{{ $dari->toDateString() }}" aria-label="Dari tanggal">
        <input type="date" name="sampai" value="{{ $sampai->toDateString() }}" aria-label="Sampai tanggal">
        <button class="btn sm" style="height:40px">Terapkan</button>
      </form>
    </div>
    <div class="tb-r">
      <a class="btn out" href="{{ route('admin.laporan', $qs + ['cetak' => 1]) }}" target="_blank" rel="noopener">Cetak Laporan</a>
      <a class="btn" href="{{ route('admin.laporan.csv', $qs) }}">Ekspor CSV</a>
    </div>
  </div>
</section>

<section class="stats" aria-label="Ringkasan periode">
  <div class="panel stat"><div class="h">Total Penjualan <span class="ic"><x-ikon n="cart" /></span></div><b>{{ $rp($ringkas['total']) }}</b></div>
  <div class="panel stat"><div class="h">Jumlah Transaksi <span class="ic"><x-ikon n="file" /></span></div><b>{{ str_pad($ringkas['transaksi'], 2, '0', STR_PAD_LEFT) }}</b></div>
  <div class="panel stat"><div class="h">Produk Terjual <span class="ic"><x-ikon n="box" /></span></div><b>{{ str_pad($ringkas['produk'], 2, '0', STR_PAD_LEFT) }}</b></div>
  <div class="panel stat"><div class="h">Pengiriman Selesai <span class="ic"><x-ikon n="truck" /></span></div><b>{{ str_pad($ringkas['selesai'], 2, '0', STR_PAD_LEFT) }}</b></div>
</section>

<section class="two">
  <div class="panel">
    <h2>Penjualan per {{ $perBulan ? 'Bulan' : 'Hari' }}</h2>
    <p class="sub">Total pembayaran lunas, {{ $dari->format('d/m/Y') }} - {{ $sampai->format('d/m/Y') }}</p>
    <div class="chart {{ count($grafik) > 14 ? 'dense' : '' }}" role="img" aria-label="Grafik penjualan">
      @foreach ($grafik as $i => $g)
        <div title="{{ $g['label'] }}: {{ $rp($g['nilai']) }}"><i class="{{ $i === $puncak ? 'hi' : '' }}" style="height:{{ max($g['nilai'] / $maks * 85, 4) }}%"></i>{{ $g['label'] }}</div>
      @endforeach
    </div>
  </div>
  <div class="panel">
    <h2>Produk Terlaris</h2>
    <p class="sub">Berdasarkan jumlah terjual (transaksi lunas)</p>
    <ul class="lowl">
      @forelse ($terlaris as $n => $p)
        <li>
          <span class="rk">{{ $n + 1 }}</span>
          <span class="th">@if ($p->gambar)<img src="{{ asset('uploads/produk/' . $p->gambar) }}" alt="">@else<x-ikon :n="\App\Support\IkonKategori::untuk($p->id_kategori)" :s="20" />@endif</span>
          <span class="nm">{{ $p->nama_produk }}<small class="mu" style="display:block;font-weight:400">{{ $p->terjual }} terjual</small></span>
        </li>
      @empty
        <li class="empty">Belum ada penjualan pada periode ini.</li>
      @endforelse
    </ul>
  </div>
</section>

<section class="panel">
  <h2>Rincian Transaksi</h2>
  <p class="sub">Transaksi pada periode yang dipilih</p>
  <div class="tw"><table>
    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Pembayaran</th></tr></thead>
    <tbody>
    @forelse ($rincian as $t)
      @php $nm = $item[$t->id_transaksi] ?? collect(); $ok = strtolower($t->status_pembayaran ?? '') === 'lunas'; @endphp
      <tr>
        <td>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
        <td>{{ $t->nama }}</td>
        <td>{{ $nm->first()->nama_produk ?? '-' }}@if ($nm->count() > 1) +{{ $nm->count() - 1 }} lainnya @endif</td>
        <td>{{ $rp($t->total) }}</td>
        <td><span class="bd {{ $ok ? 'g' : 'y' }}">{{ $ok ? 'Lunas' : 'Menunggu' }}</span></td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Tidak ada transaksi pada periode ini.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $rincian->links('vendor.pagination.raden') }}
  <div class="lap-tot"><span>Total Penjualan (transaksi lunas)</span><b>{{ $rp($ringkas['total']) }}</b></div>
</section>
@endsection
