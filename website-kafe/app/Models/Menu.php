<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'harga_jual',
        'status_aktif',
        'status_habis',
    ];

    protected function casts(): array
    {
        return [
            'harga_jual' => 'decimal:2',
            'status_aktif' => 'boolean',
            'status_habis' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($menu) {
            if (empty($menu->kode)) {
                $nomor = 1;

                do {
                    $kode = 'MNU-' . str_pad($nomor, 3, '0', STR_PAD_LEFT);

                    $sudahAda = static::where('kode', $kode)->exists();

                    $nomor++;
                } while ($sudahAda);

                $menu->kode = $kode;
            }
        });
    }

    // Relasi ke tabel Resep
    public function reseps(): HasMany
    {
        return $this->hasMany(Resep::class, 'menu_id');
    }
    
    // Relasi ke tabel DetailPesanan
    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'menu_id');
    }
}
