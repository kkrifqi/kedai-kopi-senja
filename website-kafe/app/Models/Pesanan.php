<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pesanan extends Model
{
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

    protected $casts = [
        'tanggal_pesanan' => 'datetime',
    ];

    public function detailPesanans(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pelanggan_id');
    }
}