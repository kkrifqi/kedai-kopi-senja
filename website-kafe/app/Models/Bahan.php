<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bahan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama',
        'satuan_dasar',
        'stok_sekarang',
        'batas_minimum',
        'harga_sekarang',
        'tanggal_update_harga',
    ];

    protected function casts(): array
    {
        return [
            'stok_sekarang' => 'decimal:3',
            'batas_minimum' => 'decimal:3',
            'harga_sekarang' => 'decimal:2',
            'tanggal_update_harga' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($bahan) {
            if (empty($bahan->kode)) {
                $nomor = 21;

                do {
                    $kode = 'BHN-' . str_pad($nomor, 3, '0', STR_PAD_LEFT);

                    $sudahAda = static::where('kode', $kode)->exists();

                    $nomor++;
                } while ($sudahAda);

                $bahan->kode = $kode;
            }
        });
    }

    // Relasi ke tabel Resep
    public function reseps(): HasMany
    {
        return $this->hasMany(Resep::class, 'bahan_id');
    }

    // Relasi ke tabel DetailStokMasuk
    public function detailStokMasuks(): HasMany
    {
        return $this->hasMany(DetailStokMasuk::class, 'bahan_id');
    }
}
