<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelanggan_id',
        'kasir_id',
        'no_meja',
        'metode_bayar',
        'status',
        'subtotal',
        'service',
        'pajak',
        'total_harga',
        'tanggal_pesanan',
    ];

    protected function casts(): array
    {
        return [
            'no_meja' => 'integer',
            'subtotal' => 'decimal:2',
            'service' => 'decimal:2',
            'pajak' => 'decimal:2',
            'total_harga' => 'decimal:2',
            'tanggal_pesanan' => 'datetime',
        ];
    }

    // Relasi ke pelanggan
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }

    // Relasi ke kasir
    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    // Relasi ke detail pesanan
    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }
}
