@php
  $u = auth()->user();
  $menu = [['user', 'Profil Saya', 'akun', ['akun']], ['edit', 'Biodata & Keamanan', 'akun.ubah', ['akun.ubah']], ['pin', 'Daftar Alamat', 'akun.alamat', ['akun.alamat*']], ['file', 'Riwayat Transaksi', 'pesanan', ['pesanan*']]];
@endphp
<aside class="panel akm">
  <div class="akh"><span class="av lg">{{ strtoupper(substr($u->nama, 0, 1)) }}</span><div><b>{{ $u->nama }}</b><small class="mu" style="display:block">{{ $u->no_telp }}</small></div></div>
  <nav aria-label="Menu akun">
    @foreach ($menu as [$ik, $t, $rt, $pola])
      <a href="{{ route($rt) }}" class="{{ request()->routeIs(...$pola) ? 'on' : '' }}"><x-ikon :n="$ik" :s="17" /> {{ $t }}</a>
    @endforeach
    <form method="post" action="{{ route('logout') }}">@csrf<button class="red"><x-ikon n="logout" :s="17" /> Keluar</button></form>
  </nav>
</aside>
