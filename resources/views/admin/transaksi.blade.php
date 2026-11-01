@extends('layouts.admin')
@section('title', 'Transaksi & Pembayaran')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $metode = ['tunai' => 'Tunai di Toko', 'transfer' => 'Transfer Bank', 'ewallet' => 'E-Wallet'];
@endphp
<section class="panel">
  <h2>Daftar Transaksi</h2>
  <p class="sub">Verifikasi pembayaran: saat ditandai lunas, stok produk otomatis dikurangi</p>
  <div class="tw"><table>
    <thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Metode</th><th>Pembayaran</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($transaksi as $t)
      @php $lunas = strtolower($t->status_pembayaran ?? '') === 'lunas'; @endphp
      <tr>
        <td>TRX-{{ str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ \Illuminate\Support\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
        <td>{{ $t->nama }}</td><td>{{ $rp($t->total) }}</td>
        <td>{{ $metode[$t->metode] ?? ($t->metode ?: '-') }}</td>
        <td><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></td>
        <td>
          @unless ($lunas)
            <form method="post" action="{{ route('admin.transaksi.lunas', $t->id_transaksi) }}" onsubmit="return confirm('Tandai transaksi ini lunas? Stok produk akan dikurangi.')">
              @csrf <button class="btn sm">Verifikasi Lunas</button>
            </form>
          @endunless
        </td>
      </tr>
    @empty
      <tr><td colspan="7" class="empty">Belum ada transaksi.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $transaksi->links('vendor.pagination.raden') }}
</section>
@endsection
