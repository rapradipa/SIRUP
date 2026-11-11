@extends('layouts.app')
@section('title', 'Keranjang - Raden Group')
@section('page_class', 'home')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <span>Keranjang</span></nav>
  <h1 class="pt">Keranjang Belanja</h1>

  @if ($items->isEmpty())
    <div class="panel empty-box">Keranjang Anda masih kosong. <a href="{{ route('katalog') }}">Mulai belanja</a></div>
  @else
    <form id="fc" method="post" action="{{ route('checkout') }}">@csrf</form>
    <div class="cg">
      <section class="panel cl">
        <div class="ch">
          <label class="ck"><input type="checkbox" id="all" checked> Pilih Semua (<span id="jml">{{ $items->count() }}</span>)</label>
          <button form="fc" formaction="{{ route('keranjang.hapus') }}" class="lnk red" data-ajax data-refresh>Hapus yang dipilih</button>
        </div>
        @foreach ($items as $i)
          @php $fin = $i->harga * (100 - $i->diskon) / 100; @endphp
          <div class="ci">
            <input type="checkbox" form="fc" name="ids[]" value="{{ $i->id_keranjang }}" class="sel" checked
                   data-sub="{{ $i->harga * $i->jumlah }}" data-tot="{{ $fin * $i->jumlah }}" aria-label="Pilih {{ $i->nama_produk }}">
            <span class="th">
              @if ($i->gambar)
                <img src="{{ asset('uploads/produk/' . $i->gambar) }}" alt="">
              @else
                <x-ikon :n="\App\Support\IkonKategori::untuk($i->id_kategori)" :s="24" />
              @endif
            </span>
            <div class="ib">
              <a href="{{ route('produk', $i->id_produk) }}">{{ $i->nama_produk }}</a>
              <div class="pr">{{ $rp($fin) }}</div>
              <small class="mu">Jumlah: {{ $i->jumlah }} &middot; Stok: {{ $i->stok }}</small>
            </div>
            <form method="post" action="{{ route('keranjang.ubah', $i->id_keranjang) }}" data-ajax data-refresh>
              @csrf @method('PATCH')
              <div class="qty">
                <button name="aksi" value="kurang" aria-label="Kurangi">&minus;</button>
                <span class="qv">{{ $i->jumlah }}</span>
                <button name="aksi" value="tambah" aria-label="Tambah">+</button>
              </div>
            </form>
            <form method="post" action="{{ route('keranjang.buang', $i->id_keranjang) }}" data-ajax data-refresh>
              @csrf @method('DELETE')
              <button class="lnk red">Hapus</button>
            </form>
          </div>
        @endforeach
      </section>

      <aside class="panel sm">
        <h2>Ringkasan Pesanan</h2>
        <div class="rw"><span>Subtotal (<span id="cn">0</span> barang dipilih)</span><b id="sub">Rp 0</b></div>
        <div class="rw"><span>Diskon</span><b id="dsc">- Rp 0</b></div>
        <div class="rw tt"><span>Total</span><b id="tot">Rp 0</b></div>
        <button form="fc" id="go" class="btn full">Lanjut ke Pembayaran (<span id="gn">0</span>)</button>
        <p class="mu">Pengiriman dapat dipilih setelah pembayaran lunas.</p>
      </aside>
    </div>
  @endif
</div>
@endsection
@push('scripts')
<script>
(function () {
  const rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID'), $ = id => document.getElementById(id);
  // Dihitung ulang setiap kali daftar barang berubah (dipanggil juga setelah isi halaman disegarkan lewat AJAX)
  function hitung() {
    const sel = [...document.querySelectorAll('.sel')], all = $('all');
    if (!sel.length || !all) return;
    const on = sel.filter(c => c.checked);
    const sub = on.reduce((a, c) => a + +c.dataset.sub, 0), tot = on.reduce((a, c) => a + +c.dataset.tot, 0);
    $('cn').textContent = $('gn').textContent = on.length; $('jml').textContent = sel.length;
    $('sub').textContent = rp(sub); $('dsc').textContent = '- ' + rp(sub - tot); $('tot').textContent = rp(tot);
    $('go').disabled = !on.length; all.checked = on.length === sel.length;
  }
  // Event delegation: tetap bekerja walau isi halaman diganti
  document.addEventListener('change', e => {
    if (e.target.id === 'all') { document.querySelectorAll('.sel').forEach(c => c.checked = e.target.checked); hitung(); }
    else if (e.target.classList && e.target.classList.contains('sel')) hitung();
  });
  document.addEventListener('ajax:disegarkan', hitung);
  hitung();
})();
</script>
@endpush
