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

            // Termasuk status 401 dan status selain 2xx.
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

            DB::transaction(function () use ($hasil, $tanggal, &$jumlah) {
                foreach ($hasil['data'] as $item) {
                    if (
                        ! is_array($item) ||
                        empty($item['kode']) ||
                        empty($item['nama']) ||
                        ! in_array($item['satuan_dasar'] ?? null, ['g', 'ml', 'pcs'], true) ||
                        ! isset($item['harga_per_satuan']) ||
                        ! is_numeric($item['harga_per_satuan']) ||
                        $item['harga_per_satuan'] < 0
                    ) {
                        continue;
                    }

                    Bahan::updateOrCreate(
                        ['kode' => $item['kode']],
                        [
                            'nama' => $item['nama'],
                            'satuan_dasar' => $item['satuan_dasar'],
                            'harga_sekarang' => $item['harga_per_satuan'],
                            'tanggal_update_harga' => $tanggal,
                        ]
                    );

                    $jumlah++;
                }
            });

            if ($jumlah === 0) {
                return $this->gagal('Tidak ada data harga supplier yang valid.');
            }

            return [
                'berhasil' => true,
                'jumlah' => $jumlah,
                'tanggal' => $tanggal,
                'pesan' => 'Harga bahan berhasil diperbarui.',
            ];
        } catch (Throwable $e) {
            // Jangan tampilkan detail error atau API key ke pengguna.
            report($e);

            return $this->gagal('Supplier tidak dapat dihubungi. Harga tersimpan tetap digunakan.');
        }
    }

    private function gagal(string $pesan): array
    {
        return [
            'berhasil' => false,
            'jumlah' => 0,
            'tanggal' => Bahan::max('tanggal_update_harga'),
            'pesan' => $pesan,
        ];
    }
}