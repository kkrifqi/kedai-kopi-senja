<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) =>
                        'Rp ' . number_format((float) $state, 2, ',', '.')
                    )
                    ->sortable(),

                IconColumn::make('status_aktif')
                    ->label('Aktif')
                    ->boolean(),

                IconColumn::make('status_habis')
                    ->label('Ketersediaan')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->falseIcon('heroicon-o-check-circle'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}