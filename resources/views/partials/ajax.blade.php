{{-- Kirim form tanpa reload halaman (dipakai layout pelanggan & admin).
     - Form/tombol bertanda data-ajax dikirim lewat fetch.
     - data-refresh: setelah sukses, isi halaman ([data-segarkan] atau main.page) disegarkan tanpa reload.
     - data-confirm + data-ajax: dialog konfirmasi tetap muncul, lalu dikirim lewat fetch.
     - Event 'ajax:sukses' dikirim ke form (detail = JSON balasan) agar halaman bisa menyesuaikan tampilannya.
     - Form lain tetap bekerja seperti biasa. --}}
<script>
(function(){
  var LOGIN = @json(route('login'));
  var sibuk = false;

  function toast(pesan, ok){
    if (!pesan) return;
    var box = document.getElementById('toastBox');
    if (!box) { box = document.createElement('div'); box.id = 'toastBox'; box.className = 'toast-box'; document.body.appendChild(box); }
    var t = document.createElement('div');
    t.className = 'toast ' + (ok ? 'ok' : 'bad');
    t.setAttribute('role', ok ? 'status' : 'alert');
    t.textContent = pesan;
    box.appendChild(t);
    setTimeout(function(){ t.remove(); }, 3500);
  }

  function badge(n){
    var a = document.getElementById('navKeranjang'); if (!a || typeof n !== 'number') return;
    var c = a.querySelector('.cnt');
    if (n > 0) { if (!c) { c = document.createElement('span'); c.className = 'cnt'; a.appendChild(c); } c.textContent = n; }
    else if (c) c.remove();
  }

  function bersihkan(f){
    f.querySelectorAll('.err.js').forEach(function(e){ e.remove(); });
    f.querySelectorAll('.bad').forEach(function(e){ e.classList.remove('bad'); });
  }

  // Tampilkan pesan validasi (422) tepat di bawah kolom yang bermasalah
  function galat(f, errors){
    var pertama = null;
    Object.keys(errors || {}).forEach(function(nama){
      var msg = errors[nama][0]; if (!pertama) pertama = msg;
      var el = f.querySelector('[name="' + nama + '"]'); if (!el) return;
      var box = el.closest('.f'); if (!box) return;
      el.classList.add('bad');
      var d = document.createElement('div'); d.className = 'err js'; d.textContent = msg; box.appendChild(d);
    });
    var bad = f.querySelector('.bad'); if (bad && bad.focus) bad.focus();
    return pertama;
  }

  async function segarkan(){
    var res = await fetch(location.href, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) return;
    var doc = new DOMParser().parseFromString(await res.text(), 'text/html');
    var baru = doc.querySelector('[data-segarkan]') || doc.querySelector('main.page');
    var lama = document.querySelector('[data-segarkan]') || document.querySelector('main.page');
    if (!baru || !lama) return;
    var off = [].map.call(document.querySelectorAll('.sel:not(:checked)'), function(c){ return c.value; });
    lama.innerHTML = baru.innerHTML;
    document.querySelectorAll('.sel').forEach(function(c){ if (off.indexOf(c.value) > -1) c.checked = false; });
    document.dispatchEvent(new Event('ajax:disegarkan'));
  }

  document.addEventListener('submit', async function(e){
    var f = e.target, s = e.submitter;
    if (!f || f.tagName !== 'FORM') return;
    var ajax = f.hasAttribute('data-ajax') || (s && s.hasAttribute('data-ajax'));
    if (!ajax) return;
    if (f.hasAttribute('data-confirm')) {                       // tunggu dialog konfirmasi dulu
      if (!f.hasAttribute('data-ok')) return;
      f.removeAttribute('data-ok');
    }
    e.preventDefault(); e.stopImmediatePropagation();
    if (sibuk) return;
    sibuk = true;

    var data = new FormData(f, s || undefined);          // ikut menyertakan name/value tombol (mis. aksi=tambah)
    var url = (s && s.getAttribute('formaction')) ? s.formAction : f.action;
    var refresh = f.hasAttribute('data-refresh') || (s && s.hasAttribute('data-refresh'));
    bersihkan(f);
    if (s) s.disabled = true;

    try {
      var res = await fetch(url, {
        method: 'POST', body: data, credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (res.redirected) { location.href = res.url; return; }        // mis. peran tidak sesuai
      if (res.status === 401) { location.href = LOGIN; return; }      // belum login
      if (res.status === 419) { toast('Sesi habis, halaman dimuat ulang...', false); setTimeout(function(){ location.reload(); }, 800); return; }
      var d = null; try { d = await res.json(); } catch (_) {}
      if (!d) { toast(res.status === 413 ? 'Ukuran file terlalu besar.' : 'Terjadi kesalahan pada server.', false); return; }
      if (res.status === 422) { toast(galat(f, d.errors) || d.message || 'Periksa kembali isian Anda.', false); return; }
      if (!res.ok) { toast(d.message || 'Terjadi kesalahan. Coba lagi.', false); return; }
      badge(d.jumlah_keranjang);
      toast(d.pesan, d.ok);
      f.dispatchEvent(new CustomEvent('ajax:sukses', { bubbles: true, detail: d }));
      if (refresh) await segarkan();
    } catch (err) {
      toast('Koneksi bermasalah. Coba lagi.', false);
    } finally {
      sibuk = false;
      if (s && document.contains(s)) s.disabled = false;
    }
  });
})();
</script>
