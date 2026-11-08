@extends('layouts.admin')
@section('title', 'Supplier')
@section('content')
<section class="panel">
  <div class="tbar">
    <div><h2>Data Supplier</h2><p class="sub" style="margin-bottom:0">Pemasok barang Raden Group</p></div>
    <a class="btn" href="{{ route('admin.supplier.baru') }}"><x-ikon n="plus" :s="16" /> Tambah Supplier</a>
  </div>
  <div class="tw" style="margin-top:14px"><table>
    <thead><tr><th>Nama Supplier</th><th>Alamat</th><th>No. Telepon</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse ($supplier as $s)
      <tr>
        <td><b>{{ $s->nama_supplier }}</b></td><td>{{ $s->alamat ?: '-' }}</td><td>{{ $s->no_telpon ?: '-' }}</td>
        <td class="aks">
          <a class="ib2" href="{{ route('admin.supplier.ubah', $s->id_supplier) }}" aria-label="Ubah {{ $s->nama_supplier }}"><x-ikon n="edit" :s="15" /></a>
          <form method="post" action="{{ route('admin.supplier.hapus', $s->id_supplier) }}" data-confirm="{{ 'Hapus supplier ' . $s->nama_supplier . '?' }}" data-confirm-danger>
            @csrf @method('DELETE')
            <button class="ib2 red" aria-label="Hapus {{ $s->nama_supplier }}"><x-ikon n="trash" :s="15" /></button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="4" class="empty">Belum ada supplier.</td></tr>
    @endforelse
    </tbody>
  </table></div>
  {{ $supplier->links('vendor.pagination.raden') }}
</section>
@endsection
