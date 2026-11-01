<?php

namespace App\Support;

class IkonKategori
{
    private const PETA = [1 => 'armchair', 2 => 'pot', 3 => 'home', 4 => 'lamp', 5 => 'monitor', 6 => 'bed', 7 => 'drop', 8 => 'wrench'];

    public static function untuk(?int $idKategori): string
    {
        return self::PETA[$idKategori] ?? 'tag';
    }
}
