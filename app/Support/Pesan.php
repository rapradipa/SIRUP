<?php

namespace App\Support;

class Pesan
{
    /** Pesan validasi berbahasa Indonesia untuk form admin. */
    public const VALIDASI = [
        'required' => 'Kolom ini wajib diisi.',
        'max' => 'Terlalu panjang.',
        'min' => 'Nilai terlalu kecil.',
        'numeric' => 'Harus berupa angka.',
        'integer' => 'Harus berupa bilangan bulat.',
        'exists' => 'Pilihan tidak valid.',
        'date' => 'Tanggal tidak valid.',
        'before_or_equal' => 'Tanggal tidak boleh di masa depan.',
        'regex' => 'Format tidak valid.',
        'image' => 'File harus berupa gambar.',
        'mimes' => 'Format gambar: JPG, PNG, atau WEBP.',
        'foto.max' => 'Ukuran gambar maksimal 2 MB.',
        'harga.max' => 'Harga terlalu besar.',
        'harga.min' => 'Harga tidak boleh negatif.',
        'diskon.max' => 'Diskon maksimal 90%.',
    ];
}
