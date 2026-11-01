@php
  $akhir = $p->harga * (100 - $p->diskon) / 100;
  $habis = $p->stok <= 0;
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
@endphp
<article class="pc">
  <a class="ph" href="{{ route('produk', $p->id_produk) }}">
    @if ($p->gambar)
      <img src="{{ asset('uploads/produk/' . $p->gambar) }}" alt="{{ $p->nama_produk }}">
    @else
      <span class="phi" role="img" aria-label="{{ $p->nama_kategori }}"><x-ikon :n="\App\Support\IkonKategori::untuk($p->id_kategori)" :s="52" /></span>
    @endif
    @if ($habis)
      <span class="sold">SOLD</span>
    @endif
  </a>
  <div class="cb">
    <div class="kt">{{ $p->nama_kategori }}</div>
    <a class="nm" href="{{ route('produk', $p->id_produk) }}">{{ $p->nama_produk }}</a>
    <div class="pr">{{ $rp($akhir) }}
      @if ($p->diskon)
        <span class="dc">-{{ $p->diskon }}%</span>
      @endif
    </div>
    <div class="old">
      @if ($p->diskon)
        {{ $rp($p->harga) }}
      @endif
    </div>
    <span class="bd {{ $habis ? 'r' : 'g' }}">Stok: {{ $p->stok }}</span>
    @if ($habis)
      <button type="button" class="btn dis" disabled>Stok Habis</button>
    @else
      <form method="post" action="{{ route('keranjang.tambah') }}">
        @csrf
        <input type="hidden" name="id_produk" value="{{ $p->id_produk }}">
        <button class="btn"><x-ikon n="cart" :s="16" /> Tambah ke Keranjang</button>
      </form>
    @endif
  </div>
</article>
