<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
@php
  $tgl = \Illuminate\Support\Carbon::parse($t->tanggal);
  $noInv = 'INV/' . $tgl->format('Ymd') . '/RDN/' . str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT);
  $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas';
  $rp = fn ($n) => 'Rp' . number_format($n, 0, ',', '.');
  $metode = ['tunai' => 'Tunai di Toko', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS'];
  $bankLabel = ['bri' => 'BRI', 'mandiri' => 'Mandiri'];
  $namaMetode = ($metode[$t->metode] ?? ($t->metode ?: '-')) . ($t->metode === 'transfer' && ! empty($t->bank) ? ' (' . ($bankLabel[$t->bank] ?? $t->bank) . ')' : '');
  $ambil = ($t->jenis ?? '') === 'ambil';
  $jumlahBarang = $items->sum('jumlah');
  $adaDiskon = $items->contains(fn ($i) => $i->diskon > 0);
  $daftar = $items->sum(fn ($i) => $i->harga * $i->jumlah);
  $diskon = $adaDiskon ? max(0, $daftar - $t->total) : 0;
  $tanggalId = fn ($d) => \Illuminate\Support\Carbon::parse($d)->locale('id')->translatedFormat('d F Y');
@endphp
<title>Invoice {{ $noInv }}</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;background:#eef1f6;font-family:Arial,Helvetica,sans-serif;color:#212121;font-size:13px;line-height:1.5;-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .bar{max-width:820px;margin:18px auto 0;padding:0 8px;display:flex;justify-content:space-between;gap:10px}
  .bar a,.bar button{font:inherit;font-weight:700;border-radius:8px;padding:10px 18px;border:1px solid #0b5fff;cursor:pointer;text-decoration:none}
  .bar a{background:#fff;color:#0b5fff}.bar button{background:#0b5fff;color:#fff}
  .sheet{max-width:820px;margin:14px auto 40px;background:#fff;border:1px solid #d9dee8;padding:44px 48px}
  .top{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding-bottom:18px;border-bottom:2px solid #212121}
  .brand{display:flex;align-items:center;gap:12px}.brand .lg{background:#0a2a66;border-radius:10px;padding:8px 12px;display:flex}.brand img{height:30px;display:block}
  .brand b{font-size:16px;display:block}.brand span{color:#6b7280;font-size:12px}
  .ttl{text-align:right}.ttl h1{margin:0;font-size:26px;letter-spacing:2px}.ttl .no{color:#0b5fff;font-weight:700;margin-top:2px}
  .st{display:inline-block;margin-top:8px;padding:3px 12px;border-radius:4px;font-weight:700;font-size:12px;letter-spacing:.5px;background:#e1f6ec;color:#12805c}.st.w{background:#fff1d6;color:#a15c07}
  .info{display:grid;grid-template-columns:1fr 1fr;gap:28px;padding:20px 0;border-bottom:1px solid #d9dee8}
  .info h3{margin:0 0 8px;font-size:12px;letter-spacing:1px;color:#6b7280;text-transform:uppercase}
  dl{margin:0;display:grid;grid-template-columns:150px 1fr;gap:5px 10px}dt{color:#6b7280}dd{margin:0;font-weight:700}dd.n{font-weight:400}
  table{width:100%;border-collapse:collapse;margin-top:22px}
  th{background:#f4f6fa;text-align:left;font-size:11.5px;letter-spacing:.6px;padding:10px 12px;border-top:1px solid #d9dee8;border-bottom:1px solid #d9dee8}
  th.r,td.r{text-align:right}td{padding:12px;border-bottom:1px solid #e6e9f0;vertical-align:top}
  td b{display:block}td small{color:#6b7280}
  .sum{margin:18px 0 0 auto;width:340px}.sum div{display:flex;justify-content:space-between;padding:5px 12px}
  .sum .tt{border-top:2px solid #212121;margin-top:6px;padding-top:10px;font-size:16px;font-weight:700}.sum .tt span:last-child{color:#0b5fff}
  .bayar{margin-top:18px;padding:12px 14px;background:#f4f6fa;border-radius:6px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
  .foot{margin-top:28px;padding-top:14px;border-top:1px solid #d9dee8;color:#6b7280;font-size:12px}
  @media(max-width:640px){.sheet{padding:22px 16px}.info{grid-template-columns:1fr}.top{flex-direction:column}.ttl{text-align:left}.sum{width:100%}dl{grid-template-columns:120px 1fr}}
  @media print{body{background:#fff}.bar{display:none}.sheet{margin:0;border:0;padding:0;max-width:none}@page{margin:14mm}}
</style>
</head>
<body>
<div class="bar">
  <a href="{{ route('pesanan.show', $t->id_transaksi) }}">&larr; Kembali</a>
  <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<main class="sheet">
  <header class="top">
    <div class="brand">
      <div class="lg"><img src="{{ asset('img/logo.png') }}" alt="Raden Group"></div>
      <div><b>Raden Group</b><span>Toko Perabot Rumah Tangga</span></div>
    </div>
    <div class="ttl">
      <h1>INVOICE</h1>
      <div class="no">{{ $noInv }}</div>
      <span class="st {{ $lunas ? '' : 'w' }}">{{ $lunas ? 'LUNAS' : 'BELUM LUNAS' }}</span>
    </div>
  </header>

  <section class="info">
    <div>
      <h3>Rincian Pesanan</h3>
      <dl>
        <dt>Penjual</dt><dd>Raden Group</dd>
        <dt>Pembeli</dt><dd>{{ auth()->user()->nama }}</dd>
        <dt>Tanggal Pembelian</dt><dd>{{ $tanggalId($t->tanggal) }}</dd>
        @if ($lunas && $t->dibayar_pada)
          <dt>Tanggal Pembayaran</dt><dd>{{ $tanggalId($t->dibayar_pada) }}</dd>
        @endif
        <dt>Metode Pembayaran</dt><dd>{{ $namaMetode }}</dd>
      </dl>
    </div>
    <div>
      <h3>{{ $ambil ? 'Pengambilan' : 'Alamat Pengiriman' }}</h3>
      @if ($ambil)
        <dl><dt>Metode</dt><dd>Ambil di toko</dd></dl>
        <p style="margin:8px 0 0">Raden Group<br>Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur</p>
      @elseif ($t->alamat_pengiriman)
        <p style="margin:0"><b>{{ $t->nama_penerima }}</b>@if ($t->no_telp_penerima) ({{ $t->no_telp_penerima }}) @endif<br>{{ $t->alamat_pengiriman }}</p>
        <dl style="margin-top:10px">
          <dt>Kurir</dt><dd>{{ $t->kurir ?: '-' }}</dd>
          <dt>No. Resi</dt><dd>{{ $t->no_resi ?: '-' }}</dd>
        </dl>
      @else
        <p style="margin:0;color:#6b7280">Belum ada pilihan pengiriman.</p>
      @endif
    </div>
  </section>

  <table>
    <thead><tr><th>INFO PRODUK</th><th class="r">JUMLAH</th><th class="r">HARGA SATUAN</th><th class="r">TOTAL HARGA</th></tr></thead>
    <tbody>
    @foreach ($items as $i)
      <tr>
        <td><b>{{ $i->nama_produk }}</b><small>{{ $i->nama_kategori }}</small></td>
        <td class="r">{{ $i->jumlah }}</td>
        <td class="r">{{ $rp($i->subtotal / $i->jumlah) }}</td>
        <td class="r">{{ $rp($i->subtotal) }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>

  <div class="sum">
    @if ($adaDiskon)
      <div><span>Total Harga ({{ $jumlahBarang }} Barang)</span><span>{{ $rp($daftar) }}</span></div>
      <div><span>Total Diskon</span><span>-{{ $rp($diskon) }}</span></div>
    @else
      <div><span>Total Harga ({{ $jumlahBarang }} Barang)</span><span>{{ $rp($t->total) }}</span></div>
    @endif
    <div class="tt"><span>TOTAL BELANJA</span><span>{{ $rp($t->total) }}</span></div>
  </div>

  <div class="bayar">
    <span>Metode Pembayaran: <b>{{ $namaMetode }}</b></span>
    <span>Status: <b>{{ $lunas ? 'Lunas' : 'Menunggu pembayaran' }}</b></span>
  </div>

  <footer class="foot">
    Invoice ini sah dan diproses oleh komputer. Silakan hubungi Raden Group apabila Anda membutuhkan bantuan informasi lebih lanjut.<br>
    Raden Group &middot; Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur
  </footer>
</main>
</body>
</html>
