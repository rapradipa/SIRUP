@extends('layouts.admin')
@section('title', 'Pengguna')
@section('content')
@php $kls = ['admin' => 'b', 'kasir' => 'y', 'pelanggan' => 'g']; @endphp
<section class="panel">
  <div class="tbar">
    <div><h2>Akun Pengguna</h2><p class="sub" style="margin-bottom:0">Kelola akun login admin, kasir, dan pelanggan</p></div>
    <a class="btn" href="{{ route('admin.pengguna.baru') }}"><x-ikon n="plus" :s="16" /> Tambah Staf</a>
  </div>
  <div class="tw" style="margin-top:14px"><table>
    <thead><tr><th>Nama</th><th>No. Telepon</th><th>Peran</th><th>Atur Ulang Kata Sandi</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach ($akun as $a)
      <tr>
        <td><b>{{ $a->nama ?? '-' }}</b>@if ($a->id_akun === auth()->id()) <small class="mu">(Anda)</small>@endif</td>
        <td>{{ $a->no_telp }}</td>
        <td><span class="bd {{ $kls[$a->role] ?? 'b' }}">{{ ucfirst($a->role) }}</span></td>
        <td>
          <form class="inl" method="post" action="{{ route('admin.pengguna.sandi', $a->id_akun) }}" onsubmit="return confirm('Atur ulang kata sandi akun ini?')">
            @csrf @method('PUT')
            <input type="password" name="password" placeholder="Sandi baru" minlength="8" maxlength="72" required autocomplete="new-password" aria-label="Kata sandi baru">
            <button class="btn sm out">Atur Ulang</button>
          </form>
        </td>
        <td class="aks">
          @if ($a->role !== 'pelanggan' && $a->id_akun !== auth()->id())
            <form method="post" action="{{ route('admin.pengguna.hapus', $a->id_akun) }}" onsubmit="return confirm('Hapus akun login ini?')">@csrf @method('DELETE')<button class="ib2 red" aria-label="Hapus akun"><x-ikon n="trash" :s="15" /></button></form>
          @endif
        </td>
      </tr>
    @endforeach
    </tbody>
  </table></div>
  {{ $akun->links('vendor.pagination.raden') }}
</section>
@endsection
