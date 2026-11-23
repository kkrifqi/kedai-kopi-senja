<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PesananService
{
    /**
     * Cek apakah menu bisa dipesan (aktif, punya resep, dan stok bahan cukup untuk 1 porsi).
     */
    public function apakahMenuTersedia(Menu $menu): bool
    {
        if (! $menu->status_aktif) {
            return false;
        }

        if ($menu->reseps->isEmpty()) {
            return false;
        }

        foreach ($menu->reseps as $resep) {
            if (! $resep->bahan || (float) $resep->bahan->stok_sekarang < (float) $resep->takaran) {
                return false;
            }
        }

        return true;
    }

    /**
     * Buat pesanan baru sekaligus validasi kebutuhan gabungan & potong stok.
     *
     * @param array $daftarItem [['menu_id' => 1, 'jumlah_porsi' => 2], ...]
     */
    public function buatPesanan(
        array $daftarItem,
        string $metodeBayar,
        int $noMeja,
        ?int $kasirId = null,
        ?int $pelangganId = null
    ): Pesanan {
        if (empty($daftarItem)) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu menu untuk membuat pesanan.',
            ]);
        }

        return DB::transaction(function () use ($daftarItem, $metodeBayar, $noMeja, $kasirId, $pelangganId) {
            // 1. Gabungkan jumlah jika menu_id yang sama muncul lebih dari sekali
            $itemTergabung = [];
            foreach ($daftarItem as $item) {
                $menuId = (int) $item['menu_id'];
                $jumlahPorsi = (int) $item['jumlah_porsi'];

                if ($jumlahPorsi <= 0) {
                    continue;
                }

                $itemTergabung[$menuId] = ($itemTergabung[$menuId] ?? 0) + $jumlahPorsi;
            }

            if (empty($itemTergabung)) {
                throw ValidationException::withMessages([
                    'items' => 'Jumlah pesanan harus minimal 1 porsi.',
                ]);
            }

            // 2. Ambil menu beserta resepnya
            $daftarMenu = Menu::with('reseps')
                ->whereIn('id', array_keys($itemTergabung))
                ->get()
                ->keyBy('id');

            $kebutuhanBahan = []; // [bahan_id => total_takaran]
            $barisDetail = [];
            $subtotal = 0;

            foreach ($itemTergabung as $menuId => $jumlahPorsi) {
                $menu = $daftarMenu->get($menuId);

                if (! $menu) {
                    throw ValidationException::withMessages([
                        'items' => "Menu dengan ID {$menuId} tidak ditemukan.",
                    ]);
                }

                if (! $menu->status_aktif) {
                    throw ValidationException::withMessages([
                        'items' => "Menu '{$menu->nama}' sedang nonaktif dan tidak dapat dipesan.",
                    ]);
                }

                if ($menu->reseps->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => "Menu '{$menu->nama}' belum memiliki resep sehingga tidak dapat dipesan.",
                    ]);
                }

                // Akumulasi kebutuhan bahan dari seluruh item dalam 1 pesanan
                foreach ($menu->reseps as $resep) {
                    $bahanId = $resep->bahan_id;
                    $kebutuhan = (float) $resep->takaran * $jumlahPorsi;
                    $kebutuhanBahan[$bahanId] = ($kebutuhanBahan[$bahanId] ?? 0) + $kebutuhan;
                }

                // Rekam harga jual saat transaksi dibuat (snapshot)
                $hargaTerekam = (float) $menu->harga_jual;
                $subtotal += $hargaTerekam * $jumlahPorsi;

                $barisDetail[] = [
                    'menu_id' => $menu->id,
                    'jumlah_porsi' => $jumlahPorsi,
                    'harga_satuan_terekam' => $hargaTerekam,
                ];
            }

            // 3. Kunci baris bahan agar aman jika ada 2 pesanan masuk bersamaan
            $daftarBahan = Bahan::whereIn('id', array_keys($kebutuhanBahan))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 4. Cek kecukupan stok gabungan
            $bahanKurang = [];
            foreach ($kebutuhanBahan as $bahanId => $totalButuh) {
                $bahan = $daftarBahan->get($bahanId);
                $stokTersedia = $bahan ? (float) $bahan->stok_sekarang : 0;

                if ($stokTersedia < $totalButuh) {
                    $namaBahan = $bahan ? $bahan->nama : "Bahan #{$bahanId}";
                    $satuan = $bahan ? $bahan->satuan_dasar : '';
                    $bahanKurang[] = "{$namaBahan} (butuh {$totalButuh} {$satuan}, tersisa {$stokTersedia} {$satuan})";
                }
            }

            if (! empty($bahanKurang)) {
                throw ValidationException::withMessages([
                    'stok' => 'Stok bahan tidak mencukupi: ' . implode('; ', $bahanKurang),
                ]);
            }

            // 5. Potong stok bahan
            foreach ($kebutuhanBahan as $bahanId => $totalButuh) {
                $bahan = $daftarBahan->get($bahanId);
                $bahan->stok_sekarang = (float) $bahan->stok_sekarang - $totalButuh;
                $bahan->save();
            }

            // 6. Hitung Service 5% -> Pajak 10% dari (Subtotal + Service) -> Total Harga
            $service = round($subtotal * 0.05, 2);
            $pajak = round(($subtotal + $service) * 0.10, 2);
            $totalHarga = $subtotal + $service + $pajak;

            // 7. Simpan pesanan dan detail pesanan
            $pesanan = Pesanan::create([
                'pelanggan_id' => $pelangganId,
                'kasir_id' => $kasirId,
                'no_meja' => $noMeja,
                'metode_bayar' => $metodeBayar,
                'status' => 'Baru',
                'subtotal' => $subtotal,
                'service' => $service,
                'pajak' => $pajak,
                'total_harga' => $totalHarga,
                'tanggal_pesanan' => now(),
            ]);

            foreach ($barisDetail as $detail) {
                $pesanan->detailPesanans()->create($detail);
            }

            return $pesanan->load('detailPesanans.menu');
        });
    }
}