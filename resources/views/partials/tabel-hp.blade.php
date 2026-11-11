{{-- Tampilan HP: tabel di dalam .tw otomatis jadi daftar kartu (label kolom diambil dari <th>).
     Gaya ada di public/css/app.css (bagian "Tampilan HP"); di layar lebar tabel tidak berubah. --}}
<script>
(function(){
  function labeli(){
    document.querySelectorAll('.tw table').forEach(function(t){
      var h = [].map.call(t.querySelectorAll('thead th'), function(th){ return th.textContent.replace(/\s+/g, ' ').trim(); });
      if (!h.length) return;
      t.querySelectorAll('tbody tr').forEach(function(tr){
        [].forEach.call(tr.children, function(td, i){
          if (td.colSpan > 1 || !h[i]) return;
          td.setAttribute('data-label', h[i]);
        });
      });
      t.classList.add('kartu-hp');
    });
  }
  labeli();
  document.addEventListener('ajax:disegarkan', labeli);
})();
</script>
