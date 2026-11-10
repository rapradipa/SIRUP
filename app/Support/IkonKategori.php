<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class IkonKategori
{
    // Peta lama, dipakai sebagai cadangan untuk kategori yang kolom ikonnya masih kosong.
    private const PETA = [1 => 'armchair', 2 => 'pot', 3 => 'home', 4 => 'lamp', 5 => 'monitor', 6 => 'bed', 7 => 'drop', 8 => 'wrench'];

    // Ikon yang bisa dipilih admin: kode ikon => nama yang tampil saat kursor diarahkan.
    public const PILIHAN = [
        'armchair' => 'Kursi', 'pot' => 'Panci', 'home' => 'Rumah', 'lamp' => 'Lampu',
        'monitor' => 'Elektronik', 'bed' => 'Tempat Tidur', 'drop' => 'Air', 'wrench' => 'Perkakas',
        'box' => 'Kotak', 'store' => 'Toko', 'tag' => 'Label', 'card' => 'Kartu', 'truck' => 'Truk',
        'heart' => 'Hati', 'gift' => 'Hadiah', 'book' => 'Buku', 'leaf' => 'Daun', 'zap' => 'Listrik',
        'coffee' => 'Kopi', 'utensils' => 'Alat Makan', 'shirt' => 'Pakaian', 'baby' => 'Bayi',
        'car' => 'Mobil', 'sparkles' => 'Kebersihan',
    ];

    private static ?array $cache = null;

    public static function untuk(?int $idKategori): string
    {
        self::$cache ??= self::muat();
        $id = $idKategori ?? 0;
        $ikon = self::$cache[$id] ?? self::PETA[$id] ?? 'tag';

        return array_key_exists($ikon, self::PILIHAN) ? $ikon : 'tag';
    }

    // Satu query per permintaan. Kalau kolom ikon belum ada (migration belum dijalankan), pakai peta lama.
    private static function muat(): array
    {
        try {
            return DB::table('kategori')->whereNotNull('ikon')->pluck('ikon', 'id_kategori')->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
