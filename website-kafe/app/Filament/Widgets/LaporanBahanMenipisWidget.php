<?php

namespace App\Filament\Widgets;

use App\Models\Bahan;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LaporanBahanMenipisWidget extends TableWidget
{
    protected static ?string $heading = 'Daftar Bahan Menipis';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Bahan::query()
                    ->whereColumn('stok_sekarang', '<=', 'batas_minimum')
                    ->orderBy('stok_sekarang')
            )
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stok_sekarang')
                    ->label('Stok Sekarang')
                    ->formatStateUsing(fn ($state, Bahan $record) =>
                        rtrim(rtrim(number_format((float) $state, 3, ',', '.'), '0'), ',') . ' ' . $record->satuan_dasar
                    )
                    ->color('danger')
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('batas_minimum')
                    ->label('Batas Minimum')
                    ->formatStateUsing(fn ($state, Bahan $record) =>
                        rtrim(rtrim(number_format((float) $state, 3, ',', '.'), '0'), ',') . ' ' . $record->satuan_dasar
                    )
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Bahan $record) => (float) $record->stok_sekarang <= 0 ? 'Habis' : 'Menipis')
                    ->color(fn (string $state) => $state === 'Habis' ? 'danger' : 'warning')
                    ->alignCenter(),
            ])
            ->emptyStateHeading('Semua stok bahan aman')
            ->emptyStateDescription('Belum ada bahan dengan stok di bawah atau sama dengan batas minimum.');
    }
}