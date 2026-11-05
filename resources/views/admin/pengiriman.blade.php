@extends('layouts.admin')
@section('title', 'Pengiriman')
@section('content')
@php
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<section class="panel">
  <h2>Kelola Pengiriman</h2>
  <p class="sub">Klik pesanan untuk melihat detail dan memperbarui status pengiriman. Setiap perubahan tercatat di riwayat perjalanan.</p>
  <div class="tw"><table>
    <thead><tr><th>No. Transaksi</th><th>Pelanggan</th><th>Tujuan</th><th>Status</th><th>Kurir / Resi</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($kirim as $k)
      @php $sg = strtolower($k->status_pengiriman); $ambil = $k->jenis === 'ambil'; @endphp
      <tr>
        <td><a class="lnk" href="{{ route('admin.pengiriman.show', $k->id_transaksi) }}" style="font-size:13px;font-weight:800">TRX-{{ str_pad($k->id_transaksi, 4, '0', STR_PAD_LEFT) }}</a></td>
        <td>{{ $k->nama }}</td>
        <td>{{ $ambil ? 'Ambil di toko' : \Illuminate\Support\Str::limit($k->alamat_pengiriman, 40) }}</td>
        <td><span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span></td>
        <td>{{ $k->kurir ? $k->kurir . ' / ' . $k->no_resi : '-' }}</td>
        <td class="aks"><a class="ib2" href="{{ route('admin.pengiriman.show', $k->id_transaksi) }}" title="Kelola pengiriman" aria-label="Kelola pengiriman TRX-{{ str_pad($k->id_transaksi, 4, '0', STR_PAD_LEFT) }}"><x-ikon n="edit" :s="15" /></a></td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Belum ada pengiriman. Data muncul setelah pelanggan memilih pengiriman pada pesanan yang sudah lunas.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $kirim->links('vendor.pagination.raden') }}
</section>
@endsection
