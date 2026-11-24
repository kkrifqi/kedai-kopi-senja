<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class LaporanPenjualanWidget extends TableWidget
{
    protected static ?string $heading = 'Penjualan per Menu';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table  
            ->query(
                Menu::query()
                    ->join('detail_pesanans', 'menus.id', '=', 'detail_pesanans.menu_id')
                    ->join('pesanans', 'detail_pesanans.pesanan_id', '=', 'pesanans.id')
                    ->where('pesanans.status', '!=', 'Dibatalkan')
                    ->select(
                        'menus.id',
                        'menus.nama',
                        DB::raw('SUM(detail_pesanans.jumlah_porsi) as terjual'),
                        DB::raw('SUM(detail_pesanans.jumlah_porsi * detail_pesanans.harga_satuan_terekam) as pendapatan')
                    )
                    ->groupBy('menus.id', 'menus.nama')
            )
            ->defaultSort('pendapatan', 'desc')
            ->columns([
                TextColumn::make('nama')
                    ->label('Menu')
                    ->searchable(['menus.nama'])
                    ->sortable(),

                TextColumn::make('terjual')
                    ->label('Terjual')
                    ->numeric(decimalPlaces: 0, thousandsSeparator: '.')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('pendapatan')
                    ->label('Pendapatan')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),
            ]);
    }
}