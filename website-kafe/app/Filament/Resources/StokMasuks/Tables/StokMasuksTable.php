<?php

namespace App\Filament\Resources\StokMasuks\Tables;

use App\Filament\Resources\StokMasuks\Schemas\StokMasukForm;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StokMasuksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->date('d F Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Waktu Pencatatan')
                    ->dateTime('H:i')
                    ->sortable(),

                TextColumn::make('manajer.nama')
                    ->label('Dicatat Oleh')
                    ->searchable(),

                TextColumn::make('detail_stok_masuks_count')
                    ->label('Jumlah Jenis Bahan')
                    ->counts('detailStokMasuks')
                    ->suffix(' jenis')
                    ->sortable(),
            ])
            ->defaultSort('tanggal_masuk', 'desc')
            ->filters([])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->schema(
                        fn (Schema $schema) => StokMasukForm::configure(
                            $schema,
                            readOnly: true
                        )->getComponents()
                    ),
            ])
            ->toolbarActions([]);
    }
}
