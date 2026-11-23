<?php

namespace App\Services;

use App\Models\Menu;

class ImportMenuService
{
    public function import(string $filePath): array
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException('File CSV tidak dapat dibaca.');
        }

        try {
            $header = fgetcsv($handle, 0, ',', '"', '\\');

            if ($header === false) {
                throw new \RuntimeException('File CSV kosong.');
            }

            // Menghapus UTF-8 BOM jika ada.
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);

            $header = array_map(
                fn ($value) => trim($value),
                $header
            );

            $headerWajib = [
                'kode',
                'nama_menu',
                'kategori',
                'harga_jual',
            ];

            if ($header !== $headerWajib) {
                throw new \RuntimeException(
                    'Format header CSV tidak sesuai. Header wajib: '
                    . implode(',', $headerWajib)
                );
            }

            $berhasil = 0;
            $dilewati = 0;
            $detailDilewati = [];
            $nomorBaris = 1;

            while (($row = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                $nomorBaris++;

                if (count($row) !== count($header)) {
                    $dilewati++;
                    $detailDilewati[] = "Baris {$nomorBaris}: jumlah kolom tidak sesuai.";
                    continue;
                }

                $data = array_combine($header, $row);

                $kode = trim($data['kode']);
                $nama = trim($data['nama_menu']);
                $kategori = trim($data['kategori']);
                $harga = trim($data['harga_jual']);

                if (
                    $kode === '' ||
                    $nama === '' ||
                    ! in_array($kategori, ['Makanan', 'Minuman'], true) ||
                    ! is_numeric($harga) ||
                    (float) $harga < 0
                ) {
                    $dilewati++;
                    $detailDilewati[] = "Baris {$nomorBaris}: data tidak valid.";
                    continue;
                }

                if (
                    Menu::where('kode', $kode)->exists() ||
                    Menu::whereRaw('LOWER(nama) = ?', [mb_strtolower($nama)])->exists()
                ) {
                    $dilewati++;
                    $detailDilewati[] = "Baris {$nomorBaris}: kode atau nama menu sudah terdaftar.";
                    continue;
                }

                Menu::create([
                    'kode' => $kode,
                    'nama' => $nama,
                    'kategori' => $kategori,
                    'harga_jual' => $harga,
                    'status_aktif' => true,
                    'status_habis' => false,
                ]);

                $berhasil++;
            }

            return [
                'berhasil' => $berhasil,
                'dilewati' => $dilewati,
                'detail_dilewati' => $detailDilewati,
            ];
        } finally {
            fclose($handle);
        }
    }
}
