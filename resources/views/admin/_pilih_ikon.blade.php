{{-- Pemilih ikon kategori. Parameter: $dipilih (kode ikon), $pilihan (kode => nama), $form (opsional, id form tujuan) --}}
<details class="ikd">
  <summary title="Pilih ikon" aria-label="Pilih ikon kategori"><x-ikon :n="$dipilih" :s="20" /></summary>
  <div class="ikg" role="radiogroup" aria-label="Pilihan ikon">
    @foreach ($pilihan as $kode => $nama)
      <label class="ikp" title="{{ $nama }}">
        <input type="radio" name="ikon" value="{{ $kode }}" @if (! empty($form)) form="{{ $form }}" @endif @checked($dipilih === $kode)>
        <x-ikon :n="$kode" :s="20" />
      </label>
    @endforeach
  </div>
</details>
