@extends('layouts.admin')
@section('title', 'Transaksi & Pembayaran')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $metode = ['tunai' => 'Tunai di Toko', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS'];
  $bankLabel = ['bri' => 'BRI', 'mandiri' => 'Mandiri'];
  $namaMetode = fn ($t) => ($metode[$t->metode] ?? ($t->metode ?: '-')) . ($t->metode === 'transfer' && $t->bank ? ' (' . ($bankLabel[$t->bank] ?? $t->bank) . ')' : '');
@endphp
<section class="panel">
  <div class="tbar">
    <div><h2>Daftar Transaksi</h2>
    <p class="sub" style="margin-bottom:0">Verifikasi pembayaran: saat ditandai lunas, stok produk otomatis dikurangi</p></div>
    <form class="tf" method="get" action="{{ route('admin.transaksi') }}" role="search">
      <input type="search" name="q" value="{{ $q }}" maxlength="30" placeholder="Cari no. transaksi, mis. TRX-0007" aria-label="Cari nomor transaksi">
      <button class="btn sm">Cari</button>
      @if ($q !== '')<a class="btn out sm" href="{{ route('admin.transaksi') }}">Reset</a>@endif
    </form>
  </div>
  @if ($q !== '')
    <p class="sub" style="margin:12px 0 0">{{ $transaksi->total() }} transaksi ditemukan untuk "{{ $q }}"</p>
  @endif
  <div class="tw" style="margin-top:14px"><table>
    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($transaksi as $t)
      @php $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas'; @endphp
      <tr>
        <td>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
        <td>{{ $t->nama }}</td><td>{{ $rp($t->total) }}</td>
        <td>{{ $namaMetode($t) }}
          @if ($t->bukti)
            <br><a class="lnk" href="{{ asset('uploads/bukti/' . $t->bukti) }}" target="_blank" rel="noopener" style="font-size:12px">Lihat bukti</a>
          @endif
        </td>
        <td><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></td>
        <td>
          @unless ($lunas)
            <form method="post" action="{{ route('admin.transaksi.lunas', $t->id_transaksi) }}" data-confirm="Tandai transaksi ini lunas? Stok produk akan dikurangi.">
              @csrf <button class="btn sm">Verifikasi Lunas</button>
            </form>
          @endunless
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="empty">{{ $q !== '' ? 'Transaksi dengan nomor tersebut tidak ditemukan.' : 'Belum ada transaksi.' }}</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $transaksi->links('vendor.pagination.raden') }}
</section>
@endsection
