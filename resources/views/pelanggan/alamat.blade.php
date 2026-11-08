@extends('layouts.app')
@section('title', 'Daftar Alamat - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <a href="{{ route('akun') }}">Akun Saya</a> &rsaquo; <span>Daftar Alamat</span></nav>
  <div class="ak">
    @include('pelanggan._akun_menu')
    <div>
      <div class="kh">
        <div><h1 class="pt" style="margin:0 0 2px">Daftar Alamat</h1><span class="mu">Alamat tujuan pengiriman pesanan Anda</span></div>
        <a class="btn" href="{{ route('akun.alamat.baru') }}"><x-ikon n="plus" :s="16" /> Tambah Alamat</a>
      </div>
      <div style="display:grid;gap:14px">
        @forelse ($alamat as $a)
          <article class="panel al {{ $a->utama ? 'on' : '' }}">
            <div class="al-h">
              <b>{{ $a->label }}</b>@if ($a->utama) <span class="bd g">Utama</span>@endif
              <span class="al-a">
                <a class="lnk" href="{{ route('akun.alamat.ubah', $a->id_alamat) }}">Ubah</a>
                <form method="post" action="{{ route('akun.alamat.hapus', $a->id_alamat) }}" data-confirm="{{ 'Hapus alamat ' . $a->label . '?' }}" data-confirm-danger>@csrf @method('DELETE')<button class="lnk red">Hapus</button></form>
                @unless ($a->utama)
                  <form method="post" action="{{ route('akun.alamat.utama', $a->id_alamat) }}">@csrf<button class="lnk">Jadikan Utama</button></form>
                @endunless
              </span>
            </div>
            <div><b>{{ $a->nama_penerima }} | {{ $a->no_telp }}</b></div>
            <div class="mu">{{ $a->alamat }}, {{ $a->kota }} {{ $a->kode_pos }}</div>
          </article>
        @empty
          <div class="panel empty-box">Belum ada alamat tersimpan. Alamat yang tersimpan bisa dipilih saat mengisi pengiriman.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
