<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LaporanPenjualanWidget;
use App\Filament\Widgets\LaporanBahanMenipisWidget;
use APp\Filament\Widgets\LaporanMarginMenuWidget;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Laporan';
    protected static ?string $title = 'Laporan';

    public string $tabAktif = 'penjualan';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tabPenjualan')
                ->label('Penjualan per Menu')
                ->color(fn () => $this->tabAktif === 'penjualan' ? 'warning' : 'gray')
                ->action(fn () => $this->tabAktif = 'penjualan'),

            Action::make('tabBahanMenipis')
                ->label('Bahan Menipis')
                ->color(fn () => $this->tabAktif === 'bahan_menipis' ? 'warning' : 'gray')
                ->action(fn () => $this->tabAktif = 'bahan_menipis'),

            Action::make('tabMargin')
                ->label('Margin per Menu')
                ->color(fn () => $this->tabAktif === 'margin' ? 'warning' : 'gray')
                ->action(fn () => $this->tabAktif = 'margin'),
        ];
    }

    public function getWidgets(): array
    {
        return match ($this->tabAktif) {
            'penjualan' => [LaporanPenjualanWidget::class],
            'bahan_menipis' => [LaporanBahanMenipisWidget::class], 
            'margin' => [LaporanMarginMenuWidget::class],       
            default => [LaporanPenjualanWidget::class],
        };
    }
}