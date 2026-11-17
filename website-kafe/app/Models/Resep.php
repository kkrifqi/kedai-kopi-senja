<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Resep extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'bahan_id',
        'takaran',
    ];

    protected function casts(): array
    {
        return [
            'takaran' => 'decimal:3',
        ];
    }

    // Relasi ke tabel Menu
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Relasi ke tabel Bahan
    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }
}
