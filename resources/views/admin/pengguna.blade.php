@extends('layouts.admin')
@section('title', 'Pengguna')
@section('content')
@php $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g']; @endphp
<section class="panel">
  <div class="tbar">
    <div><h2>Akun Pengguna</h2><p class="sub" style="margin-bottom:0">Klik akun untuk mengubah kata sandi atau menghapusnya</p></div>
    <a class="btn" href="{{ route('admin.pengguna.baru') }}"><x-ikon n="plus" :s="16" /> Tambah Staf</a>
  </div>
  <form class="tf" method="get" action="{{ route('admin.pengguna') }}" role="search">
    <input type="search" name="q" value="{{ $q }}" maxlength="50" placeholder="Cari nama atau no. telepon..." aria-label="Cari akun berdasarkan nama" style="flex:1;min-width:180px">
    <button class="btn sm">Cari</button>
    @if ($q !== '')<a class="btn out sm" href="{{ route('admin.pengguna') }}">Reset</a>@endif
  </form>
  @if ($q !== '')
    <p class="sub" style="margin:12px 0 0">{{ $akun->total() }} akun ditemukan untuk "{{ $q }}"</p>
  @endif
  <div class="tw" style="margin-top:14px"><table>
    <thead><tr><th>Nama</th><th>No. Telepon</th><th>Peran</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($akun as $a)
      <tr>
        <td><a class="lnk" href="{{ route('admin.pengguna.show', $a->id_akun) }}" style="font-size:14px">{{ $a->nama ?? '-' }}</a>@if ($a->id_akun === auth()->id()) <small class="mu">(Anda)</small>@endif</td>
        <td>{{ $a->no_telp }}</td>
        <td><span class="bd {{ $kls[$a->role] ?? 'b' }}">{{ ucfirst($a->role) }}</span></td>
        <td class="aks">
          <a class="ib2" href="{{ route('admin.pengguna.show', $a->id_akun) }}" title="Kelola akun" aria-label="Kelola akun {{ $a->nama }}"><x-ikon n="edit" :s="15" /></a>
          @if ($a->id_akun !== auth()->id())
            <form method="post" action="{{ route('admin.pengguna.hapus', $a->id_akun) }}" data-confirm="{{ $a->role === 'pelanggan' ? 'Hapus akun pelanggan ' . ($a->nama ?? '') . '? Riwayat transaksinya tetap tersimpan.' : 'Hapus akun ' . ($a->nama ?? '') . '? Orang ini tidak bisa masuk lagi.' }}" data-confirm-danger>@csrf @method('DELETE')<input type="hidden" name="kembali" value="{{ request()->getRequestUri() }}"><button class="ib2 red" title="Hapus akun" aria-label="Hapus akun {{ $a->nama }}"><x-ikon n="trash" :s="15" /></button></form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="4" class="empty">Tidak ada akun yang cocok dengan pencarian.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $akun->links('vendor.pagination.raden') }}
</section>
@endsection
