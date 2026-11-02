@extends('layouts.app')
@section('title', 'Kontak Kami - Raden Group')
@section('page_class', 'home')
@section('content')
<div class="wrap">
  <nav class="crumb" aria-label="Breadcrumb"><a href="{{ route('beranda') }}">Beranda</a> &rsaquo; <span>Kontak Kami</span></nav>
  <h1 class="pt" style="margin-bottom:4px">Hubungi Kami</h1>
  <p class="mu" style="margin:0 0 20px">Ada pertanyaan tentang produk, pembayaran, atau pengiriman? Hubungi tim Raden Group lewat WhatsApp.</p>

  <div class="cg kt-g">
    <section class="panel">
      <div class="wa-h"><span class="wa-i"><x-ikon n="phone" :s="26" /></span><div><h2 style="margin:0">Chat via WhatsApp</h2><span class="mu">Nomor WhatsApp: {{ $waTampil ?? 'belum diatur' }}</span></div></div>
      <p class="mu">Tim kami siap membantu pertanyaan Anda tentang produk, pembayaran, dan pengiriman. Anda akan diarahkan ke aplikasi WhatsApp.</p>
      @if ($wa)
        <a class="btn wa" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener"><x-ikon n="phone" :s="18" /> Chat via WhatsApp</a>
        <p class="mu" style="margin:16px 0 8px;font-weight:700">Atau pilih topik, pesan akan terisi otomatis:</p>
        <div class="chips">
          @foreach ($topik as $nama => $teks)
            <a class="chip2" href="https://wa.me/{{ $wa }}?text={{ rawurlencode($teks) }}" target="_blank" rel="noopener">{{ $nama }}</a>
          @endforeach
        </div>
      @else
        <div class="note y">Nomor WhatsApp toko belum diatur. Hubungi kami lewat email atau datang langsung ke toko.</div>
      @endif
    </section>

    <aside class="panel sm">
      <h2>Informasi Toko</h2>
      <ul class="inf">
        <li><span class="ic"><x-ikon n="pin" :s="17" /></span><div><small class="mu">Alamat</small><b>{{ $toko['alamat'] }}</b></div></li>
        <li><span class="ic"><x-ikon n="clock" :s="17" /></span><div><small class="mu">Jam operasional</small><b>{{ $toko['jam'] ?: 'Belum diatur' }}</b></div></li>
        <li><span class="ic"><x-ikon n="phone" :s="17" /></span><div><small class="mu">WhatsApp</small><b>{{ $waTampil ?? 'Belum diatur' }}</b></div></li>
        <li><span class="ic"><x-ikon n="mail" :s="17" /></span><div><small class="mu">Email</small><b>{{ $toko['email'] }}</b></div></li>
      </ul>
    </aside>
  </div>

  <section class="panel" style="margin-top:20px">
    <h2>Pertanyaan Umum</h2>
    <div class="faq">
      @foreach ($faq as [$tanya, $jawab])
        <details><summary>{{ $tanya }}<x-ikon n="chev" :s="16" /></summary><p>{{ $jawab }}</p></details>
      @endforeach
    </div>
  </section>
</div>
@endsection
