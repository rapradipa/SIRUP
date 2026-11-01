<?php

namespace App\Support;

class Stok
{
    public const BATAS_MENIPIS = 10;

    /** @return array{0:string,1:string} [kelas badge, label] */
    public static function status(int $stok): array
    {
        if ($stok <= 0) return ['r', 'Habis'];
        return $stok <= self::BATAS_MENIPIS ? ['y', 'Menipis'] : ['g', 'Tersedia'];
    }
}
