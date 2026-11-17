<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailStokMasuk extends Model
{
    use HasFactory;

    protected $fillable = [
        'stok_masuk_id',
        'bahan_id',
        'jumlah',
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:3',
        ];
    }

    // Relasi ke StokMasuk
    public function stokMasuk(): BelongsTo
    {
        return $this->belongsTo(StokMasuk::class, 'stok_masuk_id');
    }

    // Relasi ke Bahan
    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }
}
