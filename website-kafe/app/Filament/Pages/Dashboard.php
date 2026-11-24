<?php

namespace App\Filament\Pages;

use App\Models\Bahan;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan Manajer';

    protected string $view = 'filament.pages.dashboard';

    public string $tabAktif = 'penjualan';

    public string $inputDari = '';
    public string $inputSampai = '';

    public string $tanggalDari = '';
    public string $tanggalSampai = '';

    public function mount(): void
    {
        $awalBulan = now()->startOfMonth()->format('Y-m-d');
        $hariIni = now()->format('Y-m-d');

        $this->inputDari = $awalBulan;
        $this->inputSampai = $hariIni;
        $this->tanggalDari = $awalBulan;
        $this->tanggalSampai = $hariIni;
    }

    public function gantiTab(string $tab): void
    {
        $this->tabAktif = $tab;
    }

    public function tampilkanLaporan(): void
    {
        $this->tanggalDari = $this->inputDari ?: now()->startOfMonth()->format('Y-m-d');
        $this->tanggalSampai = $this->inputSampai ?: now()->format('Y-m-d');
    }

    public function getDataPenjualanProperty()
    {
        $mulai = Carbon::parse($this->tanggalDari)->startOfDay();
        $selesai = Carbon::parse($this->tanggalSampai)->endOfDay();

        return DB::table('detail_pesanans')
            ->join('pesanans', 'detail_pesanans.pesanan_id', '=', 'pesanans.id')
            ->join('menus', 'detail_pesanans.menu_id', '=', 'menus.id')
            ->where('pesanans.status', '!=', 'Dibatalkan')
            ->whereBetween('pesanans.tanggal_pesanan', [$mulai, $selesai])
            ->select(
                'menus.id',
                'menus.nama',
                DB::raw('SUM(detail_pesanans.jumlah_porsi) as terjual'),
                DB::raw('SUM(detail_pesanans.jumlah_porsi * detail_pesanans.harga_satuan_terekam) as pendapatan')
            )
            ->groupBy('menus.id', 'menus.nama')
            ->orderByDesc('pendapatan')
            ->get();
    }

    public function getDataBahanMenipisProperty()
    {
        return Bahan::whereColumn('stok_sekarang', '<=', 'batas_minimum')
            ->orderBy('stok_sekarang')
            ->get();
    }
}