<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Services\PesananService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    public function halamanPos(PesananService $layananPesanan)
    {
        $daftarMenu = Menu::with('reseps.bahan')
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get()
            ->map(function (Menu $menu) use ($layananPesanan) {
                return [
                    'id' => $menu->id,
                    'nama' => $menu->nama,
                    'harga_jual' => (float) $menu->harga_jual,
                    'punya_resep' => $menu->reseps->isNotEmpty(),
                    'tersedia' => $layananPesanan->apakahMenuTersedia($menu),
                ];
            });

        return view('kasir.pos', compact('daftarMenu'));
    }

    public function simpanPesanan(Request $permintaan, PesananService $layananPesanan)
    {
        $data = $permintaan->validate([
            'no_meja' => ['required', 'integer', 'min:1', 'max:10'],
            'metode_bayar' => ['required', 'in:Tunai,QRIS'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer'],
            'items.*.jumlah_porsi' => ['required', 'integer', 'min:1'],
        ]);

        $pesanan = $layananPesanan->buatPesanan(
            daftarItem: $data['items'],
            metodeBayar: $data['metode_bayar'],
            noMeja: (int) $data['no_meja'],
            kasirId: auth()->id(),
        );

        return redirect()
            ->route('kasir.pesanan.struk', $pesanan->id)
            ->with('sukses', "Pesanan #{$pesanan->id} berhasil dibuat dan stok bahan telah dipotong.");
    }

    public function tampilStruk(Pesanan $pesanan)
    {
        $pesanan->load('detailPesanans.menu');

        return view('kasir.struk', compact('pesanan'));
    }
}