@extends('layouts.admin')
@section('title', 'Stok Masuk')
@section('content')
<div class="sg">
  <form class="panel" method="post" action="{{ route('admin.stok.simpan') }}">
    @csrf
    <h2>Input Stok Masuk</h2>
    <p class="sub">Stok produk bertambah otomatis</p>
    <div class="f"><label for="id_supplier">Supplier</label>
      <select id="id_supplier" name="id_supplier" required data-cari="Ketik untuk mencari supplier..." data-kosong="Supplier tidak ditemukan" class="{{ $errors->has('id_supplier') ? 'bad' : '' }}">
        <option value="">Pilih supplier</option>
        @foreach ($supplier as $s)<option value="{{ $s->id_supplier }}" @selected((int) old('id_supplier') == $s->id_supplier)>{{ $s->nama_supplier }}</option>@endforeach
      </select>
      @error('id_supplier')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="id_produk">Produk</label>
      <select id="id_produk" name="id_produk" required data-cari="Ketik untuk mencari produk atau supplier..." data-kosong="Produk tidak ditemukan" class="{{ $errors->has('id_produk') ? 'bad' : '' }}">
        <option value="">Pilih produk</option>
        @foreach ($produk as $p)<option value="{{ $p->id_produk }}" @selected((int) old('id_produk') == $p->id_produk)>{{ $p->nama_produk }} (stok {{ $p->stok }}{{ $p->nama_supplier ? ', ' . $p->nama_supplier : '' }})</option>@endforeach
      </select>
      @error('id_produk')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="jumlah">Jumlah masuk</label>
      <input type="number" id="jumlah" name="jumlah" value="{{ old('jumlah') }}" min="1" step="1" placeholder="0" required class="{{ $errors->has('jumlah') ? 'bad' : '' }}">
      @error('jumlah')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="tanggal">Tanggal masuk</label>
      <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $errors->has('tanggal') ? 'bad' : '' }}">
      @error('tanggal')<div class="err">{{ $message }}</div>@enderror</div>
    <button class="btn full">Simpan Stok Masuk</button>
  </form>

  <section class="panel">
    <h2>Riwayat Stok Masuk</h2>
    <p class="sub">Barang yang masuk dari supplier</p>
    <form class="tf" method="get" action="{{ route('admin.stok') }}" role="search">
      <input type="search" name="q" value="{{ $q }}" maxlength="50" placeholder="Cari no. stok (SM-0001), produk, supplier" aria-label="Cari riwayat stok masuk" style="flex:1;min-width:180px">
      <button class="btn sm">Cari</button>
      @if ($q !== '')<a class="btn out sm" href="{{ route('admin.stok') }}">Reset</a>@endif
    </form>
    @if ($q !== '')
      <p class="sub" style="margin:12px 0 0">{{ $riwayat->total() }} data ditemukan untuk "{{ $q }}"</p>
    @endif
    <div class="tw" style="margin-top:14px"><table>
      <thead><tr><th>ID</th><th>Tanggal</th><th>Supplier</th><th>Produk</th><th>Jumlah</th></tr></thead>
      <tbody>
      @forelse ($riwayat as $m)
        <tr>
          <td>SM-{{ str_pad($m->id_stok_masuk, 4, '0', STR_PAD_LEFT) }}</td>
          <td>{{ \Illuminate\Support\Carbon::parse($m->tanggal)->format('d/m/Y') }}</td>
          <td>{{ $m->nama_supplier }}</td><td>{{ $m->nama_produk }}</td><td>+{{ $m->jumlah }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="empty">{{ $q !== '' ? 'Tidak ada riwayat stok masuk yang cocok.' : 'Belum ada stok masuk.' }}</td></tr>
      @endforelse
      </tbody>
    </table></div>
    {{ $riwayat->links('vendor.pagination.raden') }}
  </section>
</div>
@endsection

@push('scripts')
<script>
/* Ubah <select data-cari> menjadi kotak pilihan yang bisa dicari.
   <select> asli tetap dipakai untuk kirim form & validasi, jadi server tidak berubah. */
(function () {
  document.querySelectorAll('select[data-cari]').forEach(function (sel) {
    var wrap = document.createElement('div'); wrap.className = 'cbx';
    var inp = document.createElement('input');
    inp.type = 'text'; inp.autocomplete = 'off'; inp.spellcheck = false;
    inp.id = 'cb_' + sel.id; inp.placeholder = sel.dataset.cari;
    inp.setAttribute('role', 'combobox'); inp.setAttribute('aria-autocomplete', 'list');
    inp.setAttribute('aria-expanded', 'false'); inp.setAttribute('aria-controls', 'cbl_' + sel.id);
    if (sel.classList.contains('bad')) inp.classList.add('bad');
    var list = document.createElement('ul'); list.className = 'cbl'; list.id = 'cbl_' + sel.id; list.setAttribute('role', 'listbox'); list.hidden = true;

    sel.parentNode.insertBefore(wrap, sel);
    wrap.appendChild(inp); wrap.appendChild(list); wrap.appendChild(sel);
    sel.classList.add('cb-asli'); sel.tabIndex = -1; sel.setAttribute('aria-hidden', 'true');
    var lb = document.querySelector('label[for="' + sel.id + '"]'); if (lb) lb.htmlFor = inp.id;

    var opsi = [].slice.call(sel.options).filter(function (o) { return o.value !== ''; });
    var aktif = -1, tampil = [];

    function teks() { var o = sel.options[sel.selectedIndex]; return o && o.value !== '' ? o.text : ''; }
    function buka(b) { list.hidden = !b; inp.setAttribute('aria-expanded', b ? 'true' : 'false'); if (!b) { aktif = -1; inp.removeAttribute('aria-activedescendant'); } }
    function sorot(i) {
      var li = list.children;
      if (aktif > -1 && li[aktif]) li[aktif].classList.remove('on');
      aktif = i;
      if (i > -1 && li[i]) { li[i].classList.add('on'); li[i].scrollIntoView({ block: 'nearest' }); inp.setAttribute('aria-activedescendant', li[i].id); }
    }
    function isi(kata) {
      var k = kata.toLowerCase().split(/\s+/).filter(Boolean);
      tampil = opsi.filter(function (o) { var t = o.text.toLowerCase(); return k.every(function (w) { return t.indexOf(w) > -1; }); });
      list.innerHTML = '';
      if (!tampil.length) { var e = document.createElement('li'); e.className = 'kosong'; e.textContent = sel.dataset.kosong || 'Data tidak ditemukan'; list.appendChild(e); aktif = -1; return; }
      tampil.forEach(function (o, i) {
        var li = document.createElement('li'); li.id = list.id + '_' + i; li.setAttribute('role', 'option'); li.textContent = o.text;
        if (o.value === sel.value) li.setAttribute('aria-selected', 'true');
        li.addEventListener('mousedown', function (ev) { ev.preventDefault(); pilih(o); });
        list.appendChild(li);
      });
      sorot(-1);
    }
    function pilih(o) { sel.value = o.value; inp.value = o.text; inp.classList.remove('bad'); sel.dispatchEvent(new Event('change', { bubbles: true })); buka(false); }

    inp.value = teks();
    inp.addEventListener('focus', function () { inp.select(); isi(''); buka(true); });
    inp.addEventListener('click', function () { if (list.hidden) { isi(''); buka(true); } });
    inp.addEventListener('input', function () { isi(inp.value); buka(true); if (tampil.length) sorot(0); });
    inp.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { e.preventDefault(); if (list.hidden) { isi(inp.value === teks() ? '' : inp.value); buka(true); } if (tampil.length) sorot((aktif + 1) % tampil.length); }
      else if (e.key === 'ArrowUp') { e.preventDefault(); if (tampil.length) sorot((aktif - 1 + tampil.length) % tampil.length); }
      else if (e.key === 'Enter') { if (!list.hidden) { e.preventDefault(); if (aktif > -1 && tampil[aktif]) pilih(tampil[aktif]); } }
      else if (e.key === 'Escape') { if (!list.hidden) { e.preventDefault(); inp.value = teks(); buka(false); } }
    });
    inp.addEventListener('blur', function () {
      if (inp.value.trim() === '') sel.value = '';       // dikosongkan = batal memilih
      inp.value = teks(); buka(false);                    // teks yang tidak dipilih dikembalikan
    });
    sel.addEventListener('invalid', function () { inp.classList.add('bad'); });
  });
})();
</script>
@endpush
