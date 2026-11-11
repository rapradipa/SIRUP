@extends('layouts.admin')
@section('title', 'Kelola Akun')
@section('content')
@php $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g']; @endphp
<div style="max-width:620px;display:grid;gap:20px">
  <p class="crumb" style="margin:0"><a href="{{ route('admin.pengguna') }}">Pengguna</a> &rsaquo; <span>{{ $a->nama }}</span></p>

  <section class="panel pfh">
    <span class="av xl">{{ strtoupper(substr($a->nama ?? '?', 0, 1)) }}</span>
    <div class="pfi">
      <h1 style="font-size:22px">{{ $a->nama ?? '-' }}</h1>
      <div class="mu">{{ $a->no_telp }}</div>
      <span class="bd {{ $kls[$a->role] ?? 'b' }}">{{ ucfirst($a->role) }}</span>
      @if ($a->id_akun === auth()->id()) <span class="mu">(akun Anda)</span>@endif
    </div>
  </section>

  <form class="panel" method="post" action="{{ route('admin.pengguna.sandi', $a->id_akun) }}" novalidate
        data-confirm="Ubah kata sandi akun ini? Pengguna harus memakai kata sandi baru untuk masuk.">
    @csrf @method('PUT')
    <h2><x-ikon n="lock" :s="18" style="vertical-align:-3px" /> Ubah Kata Sandi</h2>
    <div class="f" style="margin-top:14px"><label for="password">Kata sandi baru</label>
      <div class="pw"><input type="password" id="password" name="password" autocomplete="new-password" placeholder="Minimal 8 karakter" class="{{ $errors->has('password') ? 'bad' : '' }}"><button type="button" data-lihat="password" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div>
      @error('password')<div class="err">{{ $message }}</div>@enderror</div>
    <div class="f"><label for="password_confirmation">Konfirmasi kata sandi baru</label>
      <div class="pw"><input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"><button type="button" data-lihat="password_confirmation" aria-label="Tampilkan atau sembunyikan kata sandi"><x-ikon n="eye" :s="18" /></button></div></div>
    <div class="fa"><a class="btn out" href="{{ route('admin.pengguna') }}">Kembali</a><button class="btn"><x-ikon n="check" :s="16" /> Simpan Kata Sandi</button></div>
  </form>

  @if ($a->id_akun !== auth()->id())
  <form class="panel" method="post" action="{{ route('admin.pengguna.hapus', $a->id_akun) }}"
        data-confirm="{{ $a->role === 'pelanggan' ? 'Hapus akun pelanggan ' . ($a->nama ?? '') . '? Riwayat transaksinya tetap tersimpan.' : 'Hapus akun ' . ($a->nama ?? '') . '? Orang ini tidak bisa masuk lagi.' }}" data-confirm-danger>
    @csrf @method('DELETE')
    <h2 style="color:var(--red)"><x-ikon n="trash" :s="18" style="vertical-align:-3px" /> Hapus Akun</h2>
    <p class="sub" style="margin-top:6px">
      @if ($a->role === 'pelanggan')
        Pelanggan tidak bisa masuk lagi dan keranjang serta alamat tersimpannya dihapus. Riwayat transaksi yang sudah ada tetap tersimpan. Jika belum pernah bertransaksi, seluruh datanya ikut dihapus.
      @else
        Akun staf ini tidak bisa masuk lagi ke panel. Tindakan ini tidak dapat dibatalkan.
      @endif
    </p>
    <div class="fa" style="margin-top:0"><button class="btn" style="background:var(--red);border-color:var(--red)"><x-ikon n="trash" :s="16" /> Hapus Akun Ini</button></div>
  </form>
  @endif
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-lihat]').forEach(b => b.addEventListener('click', () => {
  const i = document.getElementById(b.dataset.lihat); i.type = i.type === 'password' ? 'text' : 'password';
}));
</script>
@endpush
