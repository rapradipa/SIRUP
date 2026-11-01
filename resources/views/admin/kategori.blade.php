@extends('layouts.admin')
@section('title', 'Kategori Produk')
@section('content')
<section class="panel">
  <p class="crumb"><a href="{{ route('admin.produk') }}">Produk &amp; Kategori</a> &rsaquo; <span>Kategori</span></p>
  <h2>Kelola Kategori</h2>
  <p class="sub">Kategori yang masih dipakai produk tidak bisa dihapus</p>
  <form class="fbar" method="post" action="{{ route('admin.kategori.simpan') }}">
    @csrf
    <input type="text" name="nama_kategori" maxlength="100" placeholder="Nama kategori baru" required aria-label="Nama kategori baru">
    <button class="btn sm"><x-ikon n="plus" :s="14" /> Tambah Kategori</button>
  </form>
  @error('nama_kategori')<div class="err" style="margin:-8px 0 12px">{{ $message }}</div>@enderror
  <div class="tw"><table>
    <thead><tr><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($kategori as $k)
      <tr>
        <td>
          <form class="inl" method="post" action="{{ route('admin.kategori.perbarui', $k->id_kategori) }}">
            @csrf @method('PUT')
            <input type="text" name="nama_kategori" value="{{ $k->nama_kategori }}" maxlength="100" required style="width:260px" aria-label="Nama kategori">
            <button class="btn sm out">Simpan</button>
          </form>
        </td>
        <td>{{ $k->jumlah_produk }}</td>
        <td class="aks">
          <form method="post" action="{{ route('admin.kategori.hapus', $k->id_kategori) }}" onsubmit="return confirm({{ \Illuminate\Support\Js::from('Hapus kategori ' . $k->nama_kategori . '?') }})">
            @csrf @method('DELETE')
            <button class="ib2 red" aria-label="Hapus {{ $k->nama_kategori }}"><x-ikon n="trash" :s="15" /></button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="3" class="empty">Belum ada kategori.</td></tr>
    @endforelse
    </tbody>
  </table></div>
</section>
@endsection
