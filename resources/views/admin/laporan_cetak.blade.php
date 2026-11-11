<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
  $rp = fn ($n) => 'Rp' . number_format($n, 0, ',', '.');
  $id = fn ($d) => \Illuminate\Support\Carbon::parse($d)->locale('id')->translatedFormat('d F Y');
  $judul = $dari->eq($sampai) ? $id($dari) : $id($dari) . ' s/d ' . $id($sampai);
@endphp
<title>Laporan Penjualan {{ $judul }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#eef1f6;font-family:Arial,Helvetica,sans-serif;color:#212121;font-size:12.5px;line-height:1.5;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .bar{max-width:900px;margin:18px auto 0;padding:0 8px;display:flex;justify-content:space-between;gap:10px}
  .bar a,.bar button{font:inherit;font-weight:700;border-radius:8px;padding:10px 18px;border:1px solid #0b5fff;cursor:pointer;text-decoration:none}
  .bar a{background:#fff;color:#0b5fff}.bar button{background:#0b5fff;color:#fff}
  .sheet{max-width:900px;margin:14px auto 40px;background:#fff;border:1px solid #d9dee8;padding:40px 44px}
  header{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;padding-bottom:16px;border-bottom:2px solid #212121}
  .brand{display:flex;align-items:center;gap:12px}.lg{background:#0a2a66;border-radius:10px;padding:8px 12px;display:flex}.lg img{height:28px;display:block}
  .brand b{display:block;font-size:15px}.brand span,.meta{color:#6b7280;font-size:12px}
  h1{margin:0;font-size:22px;letter-spacing:1px;text-align:right}.meta{text-align:right}
  .sum{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:18px 0}.sum div{border:1px solid #d9dee8;border-radius:6px;padding:10px 12px}.sum small{display:block;color:#6b7280}.sum b{font-size:15px}
  h2{font-size:13px;letter-spacing:.8px;text-transform:uppercase;margin:22px 0 8px;color:#374151}
  table{width:100%;border-collapse:collapse}th{background:#f4f6fa;text-align:left;font-size:11px;letter-spacing:.5px;padding:8px 10px;border-top:1px solid #d9dee8;border-bottom:1px solid #d9dee8}
  td{padding:8px 10px;border-bottom:1px solid #e6e9f0;vertical-align:top}.r{text-align:right}tr{break-inside:avoid}
  .tot{display:flex;justify-content:space-between;margin-top:12px;padding:12px;background:#f4f6fa;border-radius:6px;font-weight:700;font-size:14px}.tot b{color:#0b5fff}
  footer{margin-top:24px;padding-top:12px;border-top:1px solid #d9dee8;color:#6b7280;font-size:11.5px}
  @media(max-width:640px){.sheet{padding:20px 14px}.sum{grid-template-columns:1fr 1fr}header{flex-direction:column}h1,.meta{text-align:left}}
  @media print{body{background:#fff}.bar{display:none}.sheet{margin:0;border:0;padding:0;max-width:none}@page{margin:12mm}}
</style>
</head>
<body>
<div class="bar">
  <a href="{{ route('admin.laporan', $qs) }}">&larr; Kembali</a>
  <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>
<main class="sheet">
  <header>
    <div class="brand"><div class="lg"><img src="{{ asset('img/logo.png') }}" alt="Raden Group"></div><div><b>Raden Group</b><span>Toko Perabot Rumah Tangga</span></div></div>
    <div><h1>LAPORAN PENJUALAN</h1><div class="meta">Periode: {{ $judul }}<br>Dicetak: {{ now()->locale('id')->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->nama }}</div></div>
  </header>

  <div class="sum">
    <div><small>Total Penjualan</small><b>{{ $rp($ringkas['total']) }}</b></div>
    <div><small>Jumlah Transaksi</small><b>{{ $ringkas['transaksi'] }}</b></div>
    <div><small>Produk Terjual</small><b>{{ $ringkas['produk'] }}</b></div>
    <div><small>Pengiriman Selesai</small><b>{{ $ringkas['selesai'] }}</b></div>
  </div>

  <h2>Produk Terlaris</h2>
  <table>
    <thead><tr><th style="width:40px">#</th><th>Produk</th><th class="r">Terjual</th></tr></thead>
    <tbody>
    @forelse ($terlaris as $n => $p)
      <tr><td>{{ $n + 1 }}</td><td>{{ $p->nama_produk }}</td><td class="r">{{ $p->terjual }}</td></tr>
    @empty
      <tr><td colspan="3" style="color:#6b7280">Belum ada penjualan pada periode ini.</td></tr>
    @endforelse
    </tbody>
  </table>

  <h2>Rincian Transaksi</h2>
  <table>
    <thead><tr><th style="width:36px">No.</th><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Item</th><th class="r">Total</th><th>Pembayaran</th></tr></thead>
    <tbody>
    @forelse ($rincian as $t)
      @php $nm = $item[$t->id_transaksi] ?? collect(); @endphp
      <tr>
        <td>{{ $loop->iteration }}</td>
        <td>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
        <td>{{ $t->nama }}</td>
        <td>{{ $nm->first()->nama_produk ?? '-' }}@if ($nm->count() > 1) +{{ $nm->count() - 1 }} lainnya @endif</td>
        <td class="r">{{ $rp($t->total) }}</td>
        <td>{{ strtolower($t->status_pembayaran ?? '') === 'lunas' ? 'Lunas' : 'Menunggu' }}</td>
      </tr>
    @empty
      <tr><td colspan="7" style="color:#6b7280">Tidak ada transaksi pada periode ini.</td></tr>
    @endforelse
    </tbody>
  </table>
  <div class="tot"><span>Total Penjualan (transaksi lunas)</span><b>{{ $rp($ringkas['total']) }}</b></div>

  <footer>Laporan ini dihasilkan otomatis oleh sistem Raden Group. Total penjualan hanya menghitung transaksi dengan pembayaran lunas.</footer>
</main>
</body>
</html>
