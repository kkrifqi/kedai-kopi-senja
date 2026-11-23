<?php

namespace App\Services;

use App\Models\Bahan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class SupplierHargaService
{
    public function sinkronkan(): array
    {
        try {
            $url = rtrim(config('services.supplier_kafe.url'), '/');
            $apiKey = config('services.supplier_kafe.api_key');

            if (empty($url) || empty($apiKey)) {
                return $this->gagal('Konfigurasi API supplier belum lengkap.');
            }

            $response = Http::acceptJson()
                ->withHeaders([
                    'X-API-Key' => $apiKey,
                ])
                ->connectTimeout(5)
                ->timeout(10)
                ->get($url . '/harga-bahan');

            if (! $response->successful()) {
                return $this->gagal('Supplier sedang tidak tersedia.');
            }

            $hasil = $response->json();

            if (
                ! is_array($hasil) ||
                ! isset($hasil['diperbarui'], $hasil['data']) ||
                ! is_array($hasil['data'])
            ) {
                return $this->gagal('Format data dari supplier tidak valid.');
            }

            $tanggal = Carbon::parse($hasil['diperbarui'])->toDateString();

            $jumlah = 0;
            $dilewati = 0;

            DB::transaction(function () use (
                $hasil,
                $tanggal,
                &$jumlah,
                &$dilewati
            ) {
                foreach ($hasil['data'] as $item) {
                    if (
                        ! is_array($item) ||
                        empty($item['kode']) ||
                        ! in_array(
                            $item['satuan_dasar'] ?? null,
                            ['g', 'ml', 'pcs'],
                            true
                        ) ||
                        ! isset($item['harga_per_satuan']) ||
                        ! is_numeric($item['harga_per_satuan']) ||
                        (float) $item['harga_per_satuan'] < 0
                    ) {
                        $dilewati++;
                        continue;
                    }

                    // Cari bahan yang sudah ada berdasarkan kode.
                    $bahan = Bahan::where('kode', $item['kode'])
                        ->first();

                    // Jika tidak ditemukan, jangan membuat bahan baru.
                    if (! $bahan) {
                        $dilewati++;
                        continue;
                    }

                    // Hanya perbarui harga dan tanggal harga.
                    $bahan->update([
                        'harga_sekarang' => $item['harga_per_satuan'],
                        'tanggal_update_harga' => $tanggal,
                    ]);

                    $jumlah++;
                }
            });

            if ($jumlah === 0) {
                return [
                    'berhasil' => false,
                    'jumlah' => 0,
                    'dilewati' => $dilewati,
                    'tanggal' => $tanggal,
                    'pesan' => 'Tidak ada harga bahan yang cocok untuk diperbarui.',
                ];
            }

            return [
                'berhasil' => true,
                'jumlah' => $jumlah,
                'dilewati' => $dilewati,
                'tanggal' => $tanggal,
                'pesan' => 'Harga bahan berhasil diperbarui.',
            ];

        } catch (Throwable $e) {
            report($e);

            return $this->gagal(
                'Supplier tidak dapat dihubungi. Harga tersimpan tetap digunakan.'
            );
        }
    }

    private function gagal(string $pesan): array
    {
        return [
            'berhasil' => false,
            'jumlah' => 0,
            'dilewati' => 0,
            'tanggal' => Bahan::max('tanggal_update_harga'),
            'pesan' => $pesan,
        ];
    }
}
