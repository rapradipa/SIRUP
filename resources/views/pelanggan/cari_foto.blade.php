@extends('layouts.app')
@section('title', 'Cari dengan Foto - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <span>Cari dengan Foto</span></nav>
  <div class="vs">
    <section class="l" aria-labelledby="jl">
      <h2 id="jl">Cari dengan Foto</h2>
      <p class="mu">Unggah foto barang untuk menemukan produk yang mirip di Raden Group.</p>
      <div class="dz" id="dz">
        <div id="dzt"><x-ikon n="upload" :s="34" /><p><b>Pilih foto barang</b><br><span class="mu">Format JPG, PNG, atau WEBP</span></p></div>
        <img id="prev" alt="Foto yang akan dicari" hidden>
      </div>
      <div class="vbtn">
        <label class="btn" for="f-unggah"><x-ikon n="upload" :s="16" /> Unggah Gambar</label>
      </div>
      <input type="file" id="f-unggah" accept="image/jpeg,image/png,image/webp" hidden>
      
    </section>

    <section class="rr" aria-labelledby="jr">
      <h2 id="jr">Hasil Pencarian Visual</h2>
      <div id="det" class="chip2 on" hidden></div>
      <div id="hasil" aria-live="polite">
        @if ($aktif)
          <p class="mu">Hasil akan muncul di sini setelah Anda memilih foto.</p>
        @else
          <div class="note y">Pencarian foto belum diaktifkan oleh toko. Gunakan pencarian dengan mengetik nama barang di kolom pencarian.</div>
        @endif
      </div>
      <p class="mu" style="font-size:12px;margin-top:16px">Pencarian visual membantu menemukan produk tanpa mengetik nama barang.</p>
    </section>
  </div>
</div>
@endsection
@push('scripts')
<script>
const $ = id => document.getElementById(id);
const TOKEN = @json(csrf_token()), URL_CARI = @json(route('cari.foto.proses')), AKTIF = @json($aktif);

// Perkecil foto di browser (maks 1024 px, JPEG) supaya cepat dan hemat kuota
function kecilkan(file, maks = 1024) {
  return new Promise((ok, gagal) => {
    const u = URL.createObjectURL(file), img = new Image();
    img.onload = () => {
      const k = Math.min(1, maks / Math.max(img.width, img.height)), c = document.createElement('canvas');
      c.width = Math.round(img.width * k); c.height = Math.round(img.height * k);
      c.getContext('2d').drawImage(img, 0, 0, c.width, c.height); URL.revokeObjectURL(u);
      c.toBlob(b => b ? ok(b) : gagal(new Error('Foto gagal diproses.')), 'image/jpeg', 0.82);
    };
    img.onerror = () => { URL.revokeObjectURL(u); gagal(new Error('File bukan gambar yang valid.')); };
    img.src = u;
  });
}
const el = (tag, cls, teks) => { const e = document.createElement(tag); if (cls) e.className = cls; if (teks !== undefined) e.textContent = teks; return e; };
const pesan = (teks, cls = 'mu') => { $('hasil').replaceChildren(el('p', cls, teks)); };

async function proses(file) {
  if (!file) return;
  $('det').hidden = true;
  let kecil;
  try { kecil = await kecilkan(file); }
  catch (e) { pesan((e.message || 'Foto gagal dibaca.') + ' Gunakan foto JPG, PNG, atau WEBP.', 'err'); return; }
  // Pratinjau selalu tampil, agar jelas bahwa foto sudah terpilih
  $('prev').src = URL.createObjectURL(kecil); $('prev').hidden = false; $('dzt').hidden = true;
  if (!AKTIF) { pesan('Pencarian foto belum diaktifkan oleh toko (GROQ_API_KEY belum diisi).', 'err'); return; }
  pesan('Mengenali barang...');
  try {
    const fd = new FormData(); fd.append('foto', kecil, 'foto.jpg'); fd.append('_token', TOKEN);
    const r = await fetch(URL_CARI, { method: 'POST', body: fd, headers: { Accept: 'application/json' } });
    const d = await r.json().catch(() => ({}));
    if (!r.ok) { pesan(d.pesan || (r.status === 419 ? 'Sesi habis. Muat ulang halaman lalu coba lagi.' : 'Terjadi kesalahan (kode ' + r.status + '). Coba lagi.'), 'err'); return; }
    tampil(d);
  } catch (e) { pesan('Tidak dapat menghubungi server. Periksa koneksi lalu coba lagi.', 'err'); }
}

function tampil(d) {
  if (d.terdeteksi) { $('det').textContent = 'Terdeteksi: ' + d.terdeteksi; $('det').hidden = false; }
  if (!d.hasil.length) { pesan('Belum ada produk yang mirip. Coba foto dari sudut yang lebih jelas, atau cari dengan mengetik nama barang.'); return; }
  const w = el('div');
  d.hasil.forEach(h => {
    const a = el('a', 'vr' + (h.utama ? ' top' : '')); a.href = h.url;
    a.append(el('b', '', h.nama), el('span', 'mu', h.kategori || ''));
    const hrg = el('span', 'pr', h.harga); if (h.diskon) hrg.append(' ', el('span', 'dc', '-' + h.diskon + '%'));
    a.append(hrg);
    const baris = el('div', 'vm');
    if (h.utama) baris.append(el('span', 'bd', 'Paling mirip'));
    baris.append(el('span', 'bd ' + (h.stok > 0 ? 'g' : 'r'), h.stok > 0 ? 'Stok: ' + h.stok : 'SOLD'));
    a.append(baris);
    if (h.alasan) a.append(el('small', 'mu', h.alasan));
    w.append(a);
  });
  $('hasil').replaceChildren(w);
}
$('f-unggah').addEventListener('change', e => { proses(e.target.files[0]); e.target.value = ''; });
</script>
@endpush
