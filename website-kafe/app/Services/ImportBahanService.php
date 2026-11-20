<?php

namespace App\Services;

use App\Models\Bahan;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportBahanService
{
    public function import(string $path): array
    {
        $berhasil = 0;
        $dilewati = 0;
        $detail = [];

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('File CSV tidak dapat dibaca.');
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '');

            if (!$header) {
                throw new \RuntimeException('File CSV kosong.');
            }

            // Hilangkan BOM dan spasi pada header.
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header = array_map('trim', $header);

            $wajib = [
                'kode',
                'nama_bahan',
                'stok_awal',
                'satuan',
                'harga_per_satuan',
            ];

            if ($header !== $wajib) {
                throw new \RuntimeException(
                    'Format header CSV tidak sesuai.'
                );
            }

            $nomorBaris = 1;

            while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $nomorBaris++;

                if ($row === [null] || $row === []) {
                    continue;
                }

                if (count($row) !== count($wajib)) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: jumlah kolom tidak sesuai.";
                    continue;
                }

                $data = array_combine($wajib, array_map('trim', $row));

                $kode = $data['kode'];
                $nama = $data['nama_bahan'];
                $satuan = strtolower($data['satuan']);

                if (
                    $kode === '' ||
                    $nama === '' ||
                    strlen($nama) > 100 ||
                    !is_numeric($data['stok_awal']) ||
                    !is_numeric($data['harga_per_satuan']) ||
                    (float) $data['stok_awal'] < 0 ||
                    (float) $data['harga_per_satuan'] < 0
                ) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: data tidak valid.";
                    continue;
                }

                $konversi = [
                    'kg' => ['dasar' => 'g', 'pengali' => 1000],
                    'g' => ['dasar' => 'g', 'pengali' => 1],
                    'liter' => ['dasar' => 'ml', 'pengali' => 1000],
                    'ml' => ['dasar' => 'ml', 'pengali' => 1],
                    'pcs' => ['dasar' => 'pcs', 'pengali' => 1],
                ];

                if (!isset($konversi[$satuan])) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: satuan tidak dikenal.";
                    continue;
                }

                // Lewati kode atau nama yang sudah ada.
                $duplikat = Bahan::where('kode', $kode)
                    ->orWhere('nama', $nama)
                    ->exists();

                if ($duplikat) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: kode atau nama sudah terdaftar.";
                    continue;
                }

                $pengali = $konversi[$satuan]['pengali'];

                try {
                    Bahan::create([
                        'kode' => $kode,
                        'nama' => $nama,
                        'satuan_dasar' => $konversi[$satuan]['dasar'],
                        'stok_sekarang' => (float) $data['stok_awal'] * $pengali,
                        'batas_minimum' => 0,
                        'harga_sekarang' => round(
                            (float) $data['harga_per_satuan'] / $pengali,
                            2
                        ),
                    ]);

                    $berhasil++;
                } catch (Throwable $e) {
                    Log::warning('Import bahan gagal', [
                        'baris' => $nomorBaris,
                        'error' => $e->getMessage(),
                    ]);

                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: gagal disimpan.";
                }
            }
        } finally {
            fclose($handle);
        }

        return [
            'berhasil' => $berhasil,
            'dilewati' => $dilewati,
            'detail' => $detail,
        ];
    }
}
