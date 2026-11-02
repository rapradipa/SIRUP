<?php

return [
    'nama' => 'Raden Group',
    'alamat' => 'Jl. Raya Caruban-Ngawi, Desa Karangjati, Kec. Karangjati, Kab. Ngawi, Jawa Timur',
    'email' => env('TOKO_EMAIL', 'radengroup738@gmail.com'),
    // Isi di .env, contoh: TOKO_WHATSAPP=081234567890 dan TOKO_JAM="Senin-Sabtu 08.00-17.00 WIB"
    'whatsapp' => env('TOKO_WHATSAPP'),
    'jam' => env('TOKO_JAM'),
];
