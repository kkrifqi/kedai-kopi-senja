<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\StokMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StokMasukService
{
    public function create(array $data, int $manajerId): StokMasuk
    {
        $details = $data['details'] ?? [];

        unset($data['details']);

        if (empty($details)) {
            throw ValidationException::withMessages([
                'data.details' => 'Minimal harus ada satu bahan.',
            ]);
        }

        // Mencegah bahan yang sama dicatat dua kali.
        $bahanIds = array_column($details, 'bahan_id');

        if (count($bahanIds) !== count(array_unique($bahanIds))) {
            throw ValidationException::withMessages([
                'data.details' => 'Bahan yang sama tidak boleh dipilih lebih dari satu kali.',
            ]);
        }

        return DB::transaction(function () use ($data, $details, $manajerId) {
            // Membuat transaksi stok masuk.
            $stokMasuk = StokMasuk::create([
                'manajer_id' => $manajerId,
                'tanggal_masuk' => $data['tanggal_masuk'],
            ]);

            foreach ($details as $index => $detail) {
                $jumlah = $detail['jumlah'] ?? 0;

                if (!is_numeric($jumlah) || $jumlah <= 0) {
                    throw ValidationException::withMessages([
                        "data.details.{$index}.jumlah" => 'Jumlah harus lebih dari 0.',
                    ]);
                }

                // Mengunci baris bahan selama proses penambahan stok.
                $bahan = Bahan::query()
                    ->lockForUpdate()
                    ->find($detail['bahan_id']);

                if (!$bahan) {
                    throw ValidationException::withMessages([
                        "data.details.{$index}.bahan_id" => 'Bahan tidak ditemukan.',
                    ]);
                }

                // Menyimpan detail transaksi.
                $stokMasuk->detailStokMasuks()->create([
                    'bahan_id' => $bahan->id,
                    'jumlah' => $jumlah,
                ]);

                // Menambahkan stok bahan.
                $bahan->increment('stok_sekarang', $jumlah);
            }

            return $stokMasuk;
        });
    }
}
