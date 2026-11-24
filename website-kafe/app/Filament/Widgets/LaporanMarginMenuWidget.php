<?php

namespace App\Filament\Widgets;

use App\Models\Menu;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;

class LaporanMarginMenuWidget extends TableWidget
{
    protected static ?string $heading = 'HPP & Margin per Menu';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Menu::query()
                    ->leftJoin('reseps', 'menus.id', '=', 'reseps.menu_id')
                    ->leftJoin('bahans', 'reseps.bahan_id', '=', 'bahans.id')
                    ->select(
                        'menus.id',
                        'menus.nama',
                        'menus.kategori',
                        'menus.harga_jual',
                        DB::raw('COUNT(reseps.id) as jumlah_resep'),
                        DB::raw('COALESCE(SUM(reseps.takaran * bahans.harga_sekarang), 0) as hpp'),
                        DB::raw('menus.harga_jual - COALESCE(SUM(reseps.takaran * bahans.harga_sekarang), 0) as margin')
                    )
                    ->groupBy('menus.id', 'menus.nama', 'menus.kategori', 'menus.harga_jual')
            )
            ->defaultSort('margin', 'desc')
            ->columns([
                TextColumn::make('nama')
                    ->label('Menu')
                    ->searchable(['menus.nama'])
                    ->sortable()
                    ->description(fn ($record) => (int) $record->jumlah_resep === 0 ? 'Belum ada resep' : null),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->searchable(['menus.kategori'])
                    ->sortable(),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('hpp')
                    ->label('HPP')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('margin')
                    ->label('Margin')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->color(fn ($state) => (float) $state < 0 ? 'danger' : 'success')
                    ->weight('semibold')
                    ->alignEnd()
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum ada data menu');
    }
}