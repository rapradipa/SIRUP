@extends('layouts.admin')
@section('title', 'Detail Pengiriman')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $kode = 'TRX-' . str_pad($g->id_transaksi, 4, '0', STR_PAD_LEFT);
  $sg = strtolower($g->status_pengiriman);
  $ambil = $g->jenis === 'ambil';
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
  $judul = ['diproses' => 'Pesanan diproses', 'dikirim' => 'Diserahkan ke kurir', 'selesai' => $ambil ? 'Pesanan diambil' : 'Pesanan diterima'];
@endphp
<p class="crumb" style="margin:0"><a href="{{ route('admin.pengiriman') }}">Pengiriman</a> &rsaquo; <span>{{ $kode }}</span></p>
<div class="cg">
  <div style="display:grid;gap:20px">
    <section class="panel">
      <div class="rw" style="padding-top:0"><h2>Perbarui Status</h2><span class="bd {{ $stg[$sg][0] ?? 'b' }}">{{ $stg[$sg][1] ?? ucfirst($sg) }}</span></div>

      @if ($sg === 'diproses' && ! $ambil)
        <p class="mu" style="margin:0 0 12px">Isi data kurir saat pesanan diserahkan untuk dikirim.</p>
        <form method="post" action="{{ route('admin.pengiriman.ubah', $g->id_transaksi) }}">
          @csrf <input type="hidden" name="aksi" value="kirim">
          <div class="fg2">
            <div class="f"><label for="kurir">Kurir</label><input type="text" id="kurir" name="kurir" value="{{ old('kurir') }}" maxlength="60" placeholder="Contoh: JNE" required></div>
            <div class="f"><label for="no_resi">No. resi</label><input type="text" id="no_resi" name="no_resi" value="{{ old('no_resi') }}" maxlength="40" required></div>
          </div>
          <div class="f"><label for="estimasi_tiba">Estimasi tiba (opsional)</label><input type="date" id="estimasi_tiba" name="estimasi_tiba" value="{{ old('estimasi_tiba') }}" min="{{ today()->toDateString() }}"></div>
          <button class="btn"><x-ikon n="truck" :s="16" /> Tandai Dikirim</button>
        </form>
      @elseif ($sg === 'diproses' && $ambil)
        <p class="mu" style="margin:0 0 12px">Pelanggan akan mengambil pesanan di toko. Tandai setelah pesanan diserahkan.</p>
        <form method="post" action="{{ route('admin.pengiriman.ubah', $g->id_transaksi) }}" onsubmit="return confirm('Tandai pesanan ini sudah diambil?')">
          @csrf <input type="hidden" name="aksi" value="selesai"><button class="btn"><x-ikon n="check" :s="16" /> Tandai Diambil</button>
        </form>
      @elseif ($sg === 'dikirim')
        <div class="note"><b>{{ $g->kurir }}</b> &middot; resi {{ $g->no_resi }}@if ($g->estimasi_tiba)<br>Estimasi tiba: {{ \Illuminate\Support\Carbon::parse($g->estimasi_tiba)->format('d/m/Y') }}@endif</div>
        <form method="post" action="{{ route('admin.pengiriman.ubah', $g->id_transaksi) }}" onsubmit="return confirm('Tandai pesanan ini sudah diterima pelanggan?')" style="margin-top:14px">
          @csrf <input type="hidden" name="aksi" value="selesai"><button class="btn"><x-ikon n="check" :s="16" /> Tandai Diterima</button>
        </form>
      @elseif ($sg === 'selesai')
        <p class="mu" style="margin:0">Pesanan ini sudah selesai. Tidak ada perubahan status lagi.</p>
      @else
        <p class="mu" style="margin:0">Belum ada tindakan yang tersedia untuk status ini.</p>
      @endif
    </section>

    <section class="panel">
      <h2>Riwayat Perjalanan</h2>
      <p class="sub">Pembaruan terbaru ada di bagian atas</p>
      @if ($log->count())
        <ol class="tl">
          @foreach ($log as $n => $l)
            <li class="{{ $n === 0 ? 'now' : '' }}">
              <i>{!! $n === 0 ? '' : '&#10003;' !!}</i>
              <b>{{ $judul[$l->status] ?? ucfirst($l->status) }}</b><small>{{ $l->keterangan }}</small>
              <time>{{ \Illuminate\Support\Carbon::parse($l->dibuat_pada)->format('d/m/Y H:i') }}</time>
            </li>
          @endforeach
        </ol>
      @else
        <p class="empty">Belum ada catatan perjalanan.</p>
      @endif
    </section>
  </div>

  <aside class="panel sm">
    <h2>Ringkasan Pesanan</h2>
    <div class="rw"><span>No. Transaksi</span><b>{{ $kode }}</b></div>
    <div class="rw"><span>Tanggal</span><b>{{ \Illuminate\Support\Carbon::parse($g->tanggal)->format('d/m/Y') }}</b></div>
    <div class="rw"><span>Pelanggan</span><b>{{ $g->pelanggan }}</b></div>
    <div class="note" style="margin-top:8px">
      @if ($ambil)<b>Ambil di toko Raden Group</b>
      @else<b>{{ $g->nama_penerima }} | {{ $g->no_telp_penerima }}</b><br>{{ $g->alamat_pengiriman }}@endif
    </div>
    @foreach ($items as $i)
      <div class="rw"><span>{{ $i->nama_produk }} &times; {{ $i->jumlah }}</span><b>{{ $rp($i->subtotal) }}</b></div>
    @endforeach
    <div class="rw tt"><span>Total</span><b>{{ $rp($g->total) }}</b></div>
  </aside>
</div>
@endsection
