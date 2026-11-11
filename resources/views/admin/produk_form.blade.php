@extends('layouts.admin')
@section('title', $p ? 'Ubah Produk' : 'Tambah Produk')
@section('content')
@php
  $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
  $nama = old('nama_produk', $p->nama_produk ?? '');
  $harga = old('harga', isset($p) ? (int) $p->harga : '');
  $diskon = old('diskon', $p->diskon ?? 0);
  $stok = old('stok', $p->stok ?? 0);
  $akhir = (float) $harga * (100 - (int) $diskon) / 100;
@endphp
<form method="post" enctype="multipart/form-data" action="{{ $p ? route('admin.produk.perbarui', $p->id_produk) : route('admin.produk.simpan') }}">
  @csrf
  @if ($p) @method('PUT') @endif
  <input type="hidden" name="kembali" value="{{ $kembali }}">
  <p class="crumb"><a href="{{ $kembali }}">Produk &amp; Kategori</a> &rsaquo; <span>{{ $p ? 'Ubah Produk' : 'Tambah Produk' }}</span></p>
  <h1 class="pt">{{ $p ? 'Ubah Produk' : 'Tambah Produk Baru' }}</h1>

  <div class="pf">
    <section class="panel">
      <h2>Informasi Produk</h2>
      <div class="f" style="margin-top:14px"><label for="nama_produk">Nama produk</label>
        <input type="text" id="nama_produk" name="nama_produk" value="{{ $nama }}" maxlength="100" placeholder="Masukkan nama produk" required class="{{ $errors->has('nama_produk') ? 'bad' : '' }}">
        @error('nama_produk')<div class="err">{{ $message }}</div>@enderror</div>
      <div class="fg2">
        <div class="f"><label for="id_kategori">Kategori</label>
          <select id="id_kategori" name="id_kategori" required class="{{ $errors->has('id_kategori') ? 'bad' : '' }}">
            <option value="">Pilih kategori</option>
            @foreach ($kategori as $k)<option value="{{ $k->id_kategori }}" @selected((int) old('id_kategori', $p->id_kategori ?? 0) == $k->id_kategori)>{{ $k->nama_kategori }}</option>@endforeach
          </select>
          @error('id_kategori')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="f"><label for="id_supplier">Supplier</label>
          <select id="id_supplier" name="id_supplier" required class="{{ $errors->has('id_supplier') ? 'bad' : '' }}">
            <option value="">Pilih supplier</option>
            @foreach ($supplier as $s)<option value="{{ $s->id_supplier }}" @selected((int) old('id_supplier', $p->id_supplier ?? 0) == $s->id_supplier)>{{ $s->nama_supplier }}</option>@endforeach
          </select>
          @error('id_supplier')<div class="err">{{ $message }}</div>@enderror</div>
      </div>
      <div class="fg2">
        <div class="f"><label for="harga">Harga (Rp)</label>
          <input type="number" id="harga" name="harga" value="{{ $harga }}" min="0" step="1" placeholder="0" required class="{{ $errors->has('harga') ? 'bad' : '' }}">
          @error('harga')<div class="err">{{ $message }}</div>@enderror</div>
        <div class="f"><label for="diskon">Diskon (%)</label>
          <input type="number" id="diskon" name="diskon" value="{{ $diskon }}" min="0" max="90" step="1" class="{{ $errors->has('diskon') ? 'bad' : '' }}">
          @error('diskon')<div class="err">{{ $message }}</div>@enderror</div>
      </div>
      @if ($p)
        <div class="f"><label>Stok saat ini</label><input type="text" value="{{ $p->stok }} unit" disabled>
          <div class="mu" style="margin-top:6px">Stok diubah lewat menu <a class="lnk" href="{{ route('admin.stok') }}">Stok Masuk</a> agar tercatat.</div></div>
      @else
        <div class="f"><label for="stok">Stok awal</label>
          <input type="number" id="stok" name="stok" value="{{ $stok }}" min="0" step="1" required class="{{ $errors->has('stok') ? 'bad' : '' }}">
          @error('stok')<div class="err">{{ $message }}</div>@enderror</div>
      @endif
      <div class="f"><label for="deskripsi">Deskripsi produk</label>
        <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000" placeholder="Tulis deskripsi produk, ukuran, bahan, atau keterangan lain.">{{ old('deskripsi', $p->deskripsi ?? '') }}</textarea>
        @error('deskripsi')<div class="err">{{ $message }}</div>@enderror</div>
    </section>

    <div style="display:grid;gap:20px;align-content:start">
      <section class="panel">
        <h2>Foto Produk</h2>
        <label class="drop" for="foto"><x-ikon n="upload" :s="26" /><b>Pilih foto produk</b><span class="mu">Format JPG, PNG, atau WEBP, maksimal 2 MB</span></label>
        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" hidden>
        @error('foto')<div class="err">{{ $message }}</div>@enderror
        @if ($p && $p->gambar)
          <label class="ck" style="margin-top:12px"><input type="checkbox" name="hapus_foto" value="1"> Hapus foto saat ini</label>
        @endif
      </section>
      <section class="panel">
        <h2>Pratinjau Kartu Produk</h2>
        <article class="pc" style="margin-top:14px">
          <div class="ph">
            <img id="pv-img" alt="" @if ($p && $p->gambar) src="{{ asset('uploads/produk/' . $p->gambar) }}" @else hidden @endif>
            <span class="phi" id="pv-ico" @if ($p && $p->gambar) hidden @endif><x-ikon n="box" :s="52" /></span>
          </div>
          <div class="cb">
            <div class="nm" id="pv-nama">{{ $nama ?: 'Nama produk' }}</div>
            <div class="pr"><span id="pv-harga">{{ $rp($akhir) }}</span> <span class="dc" id="pv-diskon" @if (! (int) $diskon) hidden @endif>-{{ (int) $diskon }}%</span></div>
            <div class="old" id="pv-lama">@if ((int) $diskon){{ $rp((float) $harga) }}@endif</div>
            <span class="bd g">Stok: <span id="pv-stok">{{ $p->stok ?? $stok }}</span></span>
            <button type="button" class="btn dis" disabled>Tambah ke Keranjang</button>
          </div>
        </article>
      </section>
    </div>
  </div>

  <div class="fa">
    <a class="btn out" href="{{ $kembali }}">Batal</a>
    <button class="btn"><x-ikon n="check" :s="16" /> Simpan Produk</button>
  </div>
</form>
@endsection
@push('scripts')
<script>
const $ = id => document.getElementById(id), rp = n => 'Rp ' + Math.round(n).toLocaleString('id-ID');
const segar = () => {
  const h = parseFloat($('harga').value) || 0, d = Math.min(90, Math.max(0, parseInt($('diskon').value) || 0));
  $('pv-nama').textContent = $('nama_produk').value || 'Nama produk';
  $('pv-harga').textContent = rp(h * (100 - d) / 100);
  $('pv-diskon').hidden = !d; $('pv-diskon').textContent = '-' + d + '%'; $('pv-lama').textContent = d ? rp(h) : '';
  if ($('stok')) $('pv-stok').textContent = parseInt($('stok').value) || 0;
};
['nama_produk', 'harga', 'diskon', 'stok'].forEach(i => $(i) && $(i).addEventListener('input', segar));
$('foto').addEventListener('change', e => {
  const f = e.target.files[0]; if (!f) return;
  const r = new FileReader(); r.onload = () => { $('pv-img').src = r.result; $('pv-img').hidden = false; $('pv-ico').hidden = true; }; r.readAsDataURL(f);
});
segar();
</script>
@endpush
