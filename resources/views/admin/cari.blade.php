@extends('layouts.admin')
@section('title', 'Hasil Pencarian')
@section('content')
@if ($q === '')
  <section class="panel"><div class="empty">Ketik kata kunci di kolom "Cari data..." lalu tekan Enter. Contoh: nama produk, nama pelanggan, no. telepon, atau nomor transaksi (TRX-0001).</div></section>
@else
  <section class="panel">
    <h2>Hasil untuk "{{ $q }}"</h2>
    <p class="sub" style="margin-bottom:0">{{ $jumlah ? $jumlah . ' data ditemukan' : 'Tidak ada data yang cocok. Coba kata kunci lain.' }}</p>
  </section>

  @foreach ($bagian as $b)
    @continue($b['total'] === 0)
    <section class="panel" style="margin-top:20px">
      <div class="tbar">
        <div><h2>{{ $b['judul'] }}</h2><p class="sub" style="margin-bottom:0">{{ $b['total'] }} data ditemukan @if ($b['total'] > $batas)(menampilkan {{ $batas }} teratas)@endif</p></div>
        <a class="btn out sm" href="{{ $b['semua'] }}">Buka halaman {{ $b['judul'] }}</a>
      </div>
      <div class="tw" style="margin-top:10px"><table>
        <thead><tr>@foreach ($b['head'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach ($b['rows'] as $baris)
          <tr>
            @foreach ($baris as $c)
              <td>
                @if (! empty($c['bd']))
                  <span class="bd {{ $c['bd'] }}">{{ $c['t'] }}</span>
                @elseif (! empty($c['href']))
                  <a class="lnk" href="{{ $c['href'] }}" style="font-size:14px">{{ $c['t'] }}</a>
                @else
                  {{ $c['t'] }}
                @endif
              </td>
            @endforeach
          </tr>
        @endforeach
        </tbody>
      </table></div>
    </section>
  @endforeach
@endif
@endsection
