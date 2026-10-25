<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Akun extends Authenticatable
{
    protected $table = 'akun';
    protected $primaryKey = 'id_akun';
    public $timestamps = false;
    protected $fillable = ['no_telp', 'password', 'role', 'id_pelanggan', 'id_karyawan'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function getNamaAttribute(): string
    {
        $tabel = $this->id_karyawan ? 'karyawan' : 'pelanggan';
        $kunci = $this->id_karyawan ? ['id_karyawan', $this->id_karyawan] : ['id_pelanggan', $this->id_pelanggan];
        return DB::table($tabel)->where(...$kunci)->value('nama') ?? $this->no_telp;
    }

    public function isStaf(): bool
    {
        return in_array($this->role, ['admin', 'kasir'], true);
    }
}
