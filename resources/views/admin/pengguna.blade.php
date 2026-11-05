@extends('layouts.admin')
@section('title', 'Pengguna')
@section('content')
@php $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g']; @endphp
<section class="panel">
  <div class="tbar">
    <div><h2>Akun Pengguna</h2><p class="sub" style="margin-bottom:0">Klik akun untuk mengubah kata sandinya</p></div>
    <a class="btn" href="{{ route('admin.pengguna.baru') }}"><x-ikon n="plus" :s="16" /> Tambah Staf</a>
  </div>
  <div class="tw" style="margin-top:14px"><table>
    <thead><tr><th>Nama</th><th>No. Telepon</th><th>Peran</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach ($akun as $a)
      <tr>
        <td><a class="lnk" href="{{ route('admin.pengguna.show', $a->id_akun) }}" style="font-size:14px">{{ $a->nama ?? '-' }}</a>@if ($a->id_akun === auth()->id()) <small class="mu">(Anda)</small>@endif</td>
        <td>{{ $a->no_telp }}</td>
        <td><span class="bd {{ $kls[$a->role] ?? 'b' }}">{{ ucfirst($a->role) }}</span></td>
        <td class="aks">
          <a class="ib2" href="{{ route('admin.pengguna.show', $a->id_akun) }}" title="Kelola akun" aria-label="Kelola akun {{ $a->nama }}"><x-ikon n="edit" :s="15" /></a>
          @if ($a->role !== 'pelanggan' && $a->id_akun !== auth()->id())
            <form method="post" action="{{ route('admin.pengguna.hapus', $a->id_akun) }}" onsubmit="return confirm('Hapus akun login ini?')">@csrf @method('DELETE')<button class="ib2 red" title="Hapus akun" aria-label="Hapus akun"><x-ikon n="trash" :s="15" /></button></form>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  {{ $akun->links('vendor.pagination.raden') }}
</section>
@endsection
