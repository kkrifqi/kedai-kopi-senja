<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Resep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ImportResepService
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

            if (! $header) {
                throw new \RuntimeException('File CSV kosong.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            $header = array_map('trim', $header);

            $wajib = ['kode_menu', 'nama_bahan', 'takaran', 'satuan'];

            if ($header !== $wajib) {
                throw new \RuntimeException('Format header CSV tidak sesuai.');
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

                $kodeMenu = $data['kode_menu'];
                $namaBahan = mb_strtolower($data['nama_bahan']);
                $takaran = $data['takaran'];
                $satuan = strtolower($data['satuan']);

                if (
                    $kodeMenu === '' ||
                    $namaBahan === '' ||
                    ! is_numeric($takaran) ||
                    (float) $takaran <= 0 ||
                    ! in_array($satuan, ['g', 'ml', 'pcs'], true)
                ) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: data resep tidak valid.";
                    continue;
                }

                // Menyamakan variasi penulisan nama bahan.
                $namaCari = match ($namaBahan) {
                    'biji kopi arabica' => 'biji kopi arabika',
                    default => $namaBahan,
                };

                // Cari menu berdasarkan kode.
                $menu = Menu::where('kode', $kodeMenu)->first();

                if (! $menu) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: menu {$kodeMenu} tidak ditemukan.";
                    continue;
                }

                // Cari bahan yang sudah terdaftar.
                $bahan = Bahan::whereRaw(
                    'LOWER(TRIM(nama)) = ?',
                    [$namaCari]
                )->first();

                if (! $bahan) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: bahan {$data['nama_bahan']} tidak ditemukan.";
                    continue;
                }

                // Pastikan satuan resep sesuai satuan dasar bahan.
                if ($bahan->satuan_dasar !== $satuan) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: satuan {$data['nama_bahan']} tidak sesuai.";
                    continue;
                }

                // Cegah resep yang sama dimasukkan dua kali.
                $duplikat = Resep::where('menu_id', $menu->id)
                    ->where('bahan_id', $bahan->id)
                    ->exists();

                if ($duplikat) {
                    $dilewati++;
                    $detail[] = "Baris {$nomorBaris}: bahan {$data['nama_bahan']} sudah ada dalam resep {$kodeMenu}.";
                    continue;
                }

                try {
                    DB::transaction(function () use (
                        $menu,
                        $bahan,
                        $takaran
                    ) {
                        Resep::create([
                            'menu_id' => $menu->id,
                            'bahan_id' => $bahan->id,
                            'takaran' => (float) $takaran,
                        ]);
                    });

                    $berhasil++;
                } catch (Throwable $e) {
                    Log::warning('Import resep gagal', [
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
