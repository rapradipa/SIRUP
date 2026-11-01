@extends('layouts.admin')
@section('title', 'Pengiriman')
@section('content')
@php
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<section class="panel">
  <h2>Kelola Pengiriman</h2>
  <p class="sub">Pesanan yang sudah lunas dan dipilih pelanggan untuk dikirim atau diambil di toko. Setiap perubahan status tercatat di riwayat perjalanan.</p>
  <div class="tw"><table>
    <thead><tr><th>No. Transaksi</th><th>Pelanggan</th><th>Tujuan</th><th>Status</th><th>Kurir / Resi</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($kirim as $k)
      @php $sg = strtolower($k->status_pengiriman); $ambil = $k->jenis === 'ambil'; @endphp
      <tr>
        <td>TRX-{{ str_pad($k->id_transaksi, 4, '0', STR_PAD_LEFT) }}</td>
        <td>{{ $k->nama }}</td>
        <td>{{ $ambil ? 'Ambil di toko' : \Illuminate\Support\Str::limit($k->alamat_pengiriman, 40) }}</td>
        <td><span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span></td>
        <td>{{ $k->kurir ? $k->kurir . ' / ' . $k->no_resi : '-' }}</td>
        <td>
          @if ($sg === 'diproses' && ! $ambil)
            <form class="inl" method="post" action="{{ route('admin.pengiriman.ubah', $k->id_transaksi) }}">
              @csrf <input type="hidden" name="aksi" value="kirim">
              <input type="text" name="kurir" placeholder="Kurir" maxlength="60" required aria-label="Kurir">
              <input type="text" name="no_resi" placeholder="No. resi" maxlength="40" required aria-label="No. resi">
              <input type="date" name="estimasi_tiba" aria-label="Estimasi tiba">
              <button class="btn sm">Tandai Dikirim</button>
            </form>
          @elseif ($sg === 'diproses' || $sg === 'dikirim')
            <form method="post" action="{{ route('admin.pengiriman.ubah', $k->id_transaksi) }}" onsubmit="return confirm('Tandai pesanan ini selesai?')">
              @csrf <input type="hidden" name="aksi" value="selesai">
              <button class="btn sm out">{{ $ambil ? 'Tandai Diambil' : 'Tandai Diterima' }}</button>
            </form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="empty">Belum ada pengiriman. Data muncul setelah pelanggan memilih pengiriman pada pesanan yang sudah lunas.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $kirim->links('vendor.pagination.raden') }}
</section>
@endsection
