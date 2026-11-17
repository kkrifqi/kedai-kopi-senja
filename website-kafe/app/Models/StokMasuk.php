<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'manajer_id',
        'tanggal_masuk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
        ];
    }

    // Relasi ke Manajer
    public function manajer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manajer_id');
    }

    // Relasi ke DetailStokMasuk
    public function detailStokMasuks(): HasMany
    {
        return $this->hasMany(DetailStokMasuk::class, 'stok_masuk_id');
    }
}
