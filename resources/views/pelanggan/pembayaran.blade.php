@extends('layouts.app')
@section('title', 'Pembayaran - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $lunas = strtolower($t->status_pembayaran) === 'lunas';
  $metode = ['tunai' => 'Tunai di Toko', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS'];
  $bankLabel = ['bri' => 'Bank BRI', 'mandiri' => 'Bank Mandiri'];
  $butuhDetail = in_array($t->metode, ['qris', 'transfer'], true);
  $kode = 'TRX-' . str_pad($t->id_transaksi, 4, '0', STR_PAD_LEFT);
  $d0 = $tersimpan->first();
  $stg = ['diproses' => ['b', 'Diproses'], 'dikirim' => ['b', 'Dikirim'], 'selesai' => ['g', 'Selesai'], 'menunggu' => ['y', 'Menunggu']];
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('keranjang') }}">Keranjang</a> &rsaquo; <span>Pembayaran</span>@if ($lunas) &rsaquo; <span>Alamat Pengiriman</span>@endif</nav>
  <h1 class="pt">Pembayaran</h1>

  @if ($lunas)
    <div class="banner"><b>Pembayaran Lunas</b><span>Transaksi {{ $kode }} sudah dibayar.@unless ($g) Isi alamat jika ingin menggunakan layanan pengiriman.@endunless</span></div>
  @endif

  <div class="cg cg-sum-first">
    @if (! $lunas)
      <div style="display:grid;gap:24px">
      <form id="fb" class="panel" method="post" action="{{ route('pembayaran.bayar', $t->id_transaksi) }}" autocomplete="off">
        @csrf
        <h2>Pilih Metode Pembayaran</h2>
        @foreach ($metode as $v => $l)
          <label class="rad"><input type="radio" name="metode" class="jm" value="{{ $v }}" @checked(old('metode', $t->metode) === $v) required> {{ $l }}</label>
          @if ($v === 'transfer')
            <div id="bankBox" class="subbank" @if (old('metode', $t->metode) !== 'transfer') hidden @endif>
              @foreach ($bankLabel as $bv => $bl)
                <label class="rad"><input type="radio" name="bank" value="{{ $bv }}" @checked(old('bank', $t->bank) === $bv)> {{ $bl }}</label>
              @endforeach
              @error('bank')<div class="err">{{ $message }}</div>@enderror
            </div>
          @endif
        @endforeach
        @error('metode')<div class="err">{{ $message }}</div>@enderror
        <div class="note">Setelah pembayaran diverifikasi, status transaksi berubah menjadi Lunas.</div>
        @if ($t->metode === 'tunai')
          <div class="note y">Metode tercatat: Tunai di Toko. Menunggu verifikasi kasir.</div>
        @endif
      </form>

      @if ($butuhDetail)
        <section class="panel">
          @if ($t->metode === 'qris')
            <h2>Bayar dengan QRIS</h2>
            <p class="mu">Pindai kode di bawah ini memakai aplikasi e-wallet atau m-banking Anda.</p>
            <div style="display:flex;justify-content:center;margin:14px 0">
              <img src="{{ asset(config('toko.qris_gambar')) }}" alt="Kode QRIS {{ config('toko.nama') }}" style="max-width:260px;width:100%;border-radius:14px;border:1px solid var(--line)">
            </div>
          @else
            @php $rek = config('toko.rekening.' . $t->bank); @endphp
            <h2>Transfer ke {{ $rek['label'] ?? $bankLabel[$t->bank] ?? 'Bank' }}</h2>
            <p class="mu">Transfer sesuai total tagihan ke rekening berikut, lalu kirim bukti transfer.</p>
            <div class="note" style="margin-top:14px">
              <div style="font-size:12px;font-weight:600;opacity:.8">No. Rekening</div>
              <div style="font-size:19px;letter-spacing:.5px">{{ $rek['nomor'] ?? 'Belum diatur admin' }}</div>
              <div style="margin-top:8px;font-size:13px">a.n. {{ $rek['nama'] ?? config('toko.nama') }}</div>
            </div>
          @endif

          @if ($t->bukti)
            <div class="note g">Bukti pembayaran terkirim. Menunggu verifikasi kasir.</div>
            <p class="mu" style="margin-top:8px"><a class="lnk" href="{{ asset('uploads/bukti/' . $t->bukti) }}" target="_blank" rel="noopener">Lihat bukti yang sudah dikirim</a></p>
            <p class="mu" style="margin-top:10px">Kirim ulang jika bukti sebelumnya salah atau kurang jelas:</p>
          @else
            <div class="note">Setelah membayar, unggah bukti pembayaran agar kasir dapat memverifikasi.</div>
          @endif

          <form method="post" action="{{ route('pembayaran.bukti', $t->id_transaksi) }}" enctype="multipart/form-data" style="margin-top:14px">
            @csrf
            <label class="drop" for="bukti"><x-ikon n="upload" :s="24" /><b>{{ $t->bukti ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }}</b><span class="mu">Format JPG, PNG, atau WEBP, maksimal 2 MB</span></label>
            <input type="file" id="bukti" name="bukti" accept="image/jpeg,image/png,image/webp" hidden required>
            @error('bukti')<div class="err">{{ $message }}</div>@enderror
            <button class="btn full" style="margin-top:14px">Kirim Bukti Pembayaran</button>
          </form>
        </section>
      @endif
      </div>
    @elseif ($g)
      <section class="panel">
        <div class="rw"><h2>Pengiriman</h2>@php [$wr, $tx] = $stg[strtolower($g->status_pengiriman)] ?? ['b', ucfirst($g->status_pengiriman)]; @endphp<span class="bd {{ $wr }}">{{ $tx }}</span></div>
        <div class="rw"><span>Jenis</span><b>{{ $g->jenis === 'ambil' ? 'Ambil di toko' : 'Dikirim ke alamat' }}</b></div>
        @if ($g->jenis !== 'ambil')
          <div class="note"><b>{{ $g->nama_penerima }} | {{ $g->no_telp_penerima }}</b><br>{{ $g->alamat_pengiriman }}</div>
        @endif
        <div class="acts" style="margin-top:16px">
          <a class="btn out" href="{{ route('pesanan.show', $t->id_transaksi) }}">Lihat Detail Transaksi</a>
          @if ($g->jenis !== 'ambil')
            <a class="btn" href="{{ route('pesanan.lacak', $t->id_transaksi) }}">Lacak Pengiriman</a>
          @endif
        </div>
      </section>
    @else
      <form id="fa" class="panel" method="post" action="{{ route('pembayaran.alamat', $t->id_transaksi) }}">
        @csrf
        <h2>Alamat Pengiriman</h2>
        @if ($tersimpan->count())
          <div class="f" style="margin-top:12px"><label for="pilih">Pakai alamat tersimpan</label>
            <select id="pilih">
              <option value="">Isi alamat baru</option>
              @foreach ($tersimpan as $a)
                <option value="{{ $a->id_alamat }}" data-nama="{{ $a->nama_penerima }}" data-telp="{{ $a->no_telp }}" data-alamat="{{ $a->alamat }}" data-kota="{{ $a->kota }}" data-pos="{{ $a->kode_pos }}" @selected($loop->first && ! old('alamat'))>{{ $a->label }}{{ $a->utama ? ' (Utama)' : '' }} - {{ \Illuminate\Support\Str::limit($a->alamat, 40) }}</option>
              @endforeach
            </select>
          </div>
        @endif
        <div class="fg2">
          <div class="f"><label for="nama_penerima">Nama penerima</label>
            <input type="text" id="nama_penerima" name="nama_penerima" value="{{ old('nama_penerima', $d0->nama_penerima ?? auth()->user()->nama) }}" placeholder="Nama penerima" required class="{{ $errors->has('nama_penerima') ? 'bad' : '' }}">
            @error('nama_penerima')<div class="err">{{ $message }}</div>@enderror</div>
          <div class="f"><label for="no_telp_penerima">Nomor telepon</label>
            <input type="text" id="no_telp_penerima" name="no_telp_penerima" value="{{ old('no_telp_penerima', $d0->no_telp ?? auth()->user()->no_telp) }}" inputmode="numeric" placeholder="08xxxxxxxxxx" required class="{{ $errors->has('no_telp_penerima') ? 'bad' : '' }}">
            @error('no_telp_penerima')<div class="err">{{ $message }}</div>@enderror</div>
        </div>
        <div class="f"><label for="alamat">Alamat lengkap</label>
          <textarea id="alamat" name="alamat" rows="3" placeholder="Jalan, nomor rumah, RT/RW, desa/kelurahan" required class="{{ $errors->has('alamat') ? 'bad' : '' }}">{{ old('alamat', $d0->alamat ?? '') }}</textarea>
          @error('alamat')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="fg2">
          <div class="f"><label for="kota">Kota / Kabupaten</label>
            <input type="text" id="kota" name="kota" value="{{ old('kota', $d0->kota ?? '') }}" placeholder="Kota / Kabupaten" required class="{{ $errors->has('kota') ? 'bad' : '' }}">
            @error('kota')<div class="err">{{ $message }}</div>@enderror</div>
          <div class="f"><label for="kode_pos">Kode pos</label>
            <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $d0->kode_pos ?? '') }}" inputmode="numeric" maxlength="5" placeholder="Kode pos" required class="{{ $errors->has('kode_pos') ? 'bad' : '' }}">
            @error('kode_pos')<div class="err">{{ $message }}</div>@enderror</div>
        </div>
        <div class="acts">
          <button name="jenis" value="ambil" formnovalidate class="btn out">Ambil di Toko</button>
          <button name="jenis" value="kirim" class="btn">Simpan Alamat &amp; Kirim</button>
        </div>
      </form>
    @endif

    <aside class="panel sm">
      <div class="rw"><h2>{{ $lunas ? 'Invoice' : 'Detail Transaksi' }}</h2><span class="bd {{ $lunas ? 'g' : 'y' }}">{{ $lunas ? 'Lunas' : 'Menunggu' }}</span></div>
      <p class="mu">No. Transaksi: {{ $kode }}</p>
      @foreach ($items as $i)
        <div class="ci slim">
          <span class="th">
            @if ($i->gambar)
              <img src="{{ asset('uploads/produk/' . $i->gambar) }}" alt="">
            @else
              <x-ikon :n="\App\Support\IkonKategori::untuk($i->id_kategori)" :s="22" />
            @endif
          </span>
          <div class="ib"><b>{{ $i->nama_produk }}</b><div class="pr">{{ $rp($i->subtotal) }}</div><small class="mu">Jumlah: {{ $i->jumlah }}</small></div>
        </div>
      @endforeach
      <div class="rw tt"><span>{{ $lunas ? 'Total Dibayar' : 'Total Tagihan' }}</span><b>{{ $rp($t->total) }}</b></div>
      @unless ($lunas)
        <button form="fb" class="btn full">{{ $t->metode ? 'Simpan Metode' : 'Bayar Sekarang' }}</button>
      @endunless
      <p class="mu"><a href="{{ route('pesanan') }}" style="color:var(--blue);font-weight:700">Lihat semua pesanan saya</a></p>
    </aside>
  </div>
</div>
@endsection
@push('scripts')
<script>
const bankBox = document.getElementById('bankBox');
if (bankBox) document.querySelectorAll('input.jm').forEach(r => r.addEventListener('change', () => {
  bankBox.hidden = r.value !== 'transfer';
}));

const pilih = document.getElementById('pilih');
if (pilih) pilih.addEventListener('change', () => {
  const o = pilih.selectedOptions[0], set = (id, v) => document.getElementById(id).value = v || '';
  set('nama_penerima', o.dataset.nama); set('no_telp_penerima', o.dataset.telp); set('alamat', o.dataset.alamat);
  set('kota', o.dataset.kota); set('kode_pos', o.dataset.pos);
});
</script>
@endpush
