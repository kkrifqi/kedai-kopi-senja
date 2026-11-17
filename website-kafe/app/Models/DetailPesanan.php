<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPesanan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pesanan_id',
        'menu_id',
        'jumlah_porsi',
        'harga_satuan_terekam',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_porsi' => 'integer',
            'harga_satuan_terekam' => 'decimal:2',
        ];
    }

    // Relasi ke Pesanan
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    // Relasi ke Menu
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
