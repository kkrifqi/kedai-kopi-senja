<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'role',
        'username',
        'nomor_hp',
        'password',
        'wajib_ganti_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    // Relasi sebagai pelanggan
    public function pesanansSebagaiPelanggan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'pelanggan_id');
    }

    // Relasi sebagai kasir
    public function pesanansSebagaiKasir(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'kasir_id');
    }

    // Relasi sebagai manajer
    public function stokMasuks(): HasMany
    {
        return $this->hasMany(StokMasuk::class, 'manajer_id');
    }
}
