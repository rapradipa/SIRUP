<?php

return [
    'nama' => 'Raden Group',
    'alamat' => 'Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur',
    'email' => env('TOKO_EMAIL', 'radengroup738@gmail.com'),
    // Isi di .env, contoh: TOKO_WHATSAPP=081234567890 dan TOKO_JAM="Senin-Sabtu 08.00-17.00 WIB"
    'whatsapp' => env('TOKO_WHATSAPP'),
    'jam' => env('TOKO_JAM'),
    // Ganti public/img/qris.png dengan kode QRIS resmi dari bank/e-wallet toko Anda.
    'qris_gambar' => env('TOKO_QRIS_GAMBAR', 'img/qris.png'),

    // Isi nomor rekening asli di .env, contoh: TOKO_REK_BRI=1234-5678-9012-345
    'rekening' => [
        'bri' => ['label' => 'Bank BRI', 'nomor' => env('TOKO_REK_BRI'), 'nama' => env('TOKO_REK_BRI_NAMA', env('TOKO_REK_NAMA', 'Raden Group'))],
        'mandiri' => ['label' => 'Bank Mandiri', 'nomor' => env('TOKO_REK_MANDIRI'), 'nama' => env('TOKO_REK_MANDIRI_NAMA', env('TOKO_REK_NAMA', 'Raden Group'))],
    ],
];
