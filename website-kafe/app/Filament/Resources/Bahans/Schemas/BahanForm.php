<?php

namespace App\Filament\Resources\Bahans\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BahanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Bahan')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                Select::make('satuan_dasar')
                    ->label('Satuan Dasar')
                    ->options([
                        'g' => 'Gram (g)',
                        'ml' => 'Mililiter (ml)',
                        'pcs' => 'Pieces (pcs)',
                    ])
                    ->required(),

                TextInput::make('stok_sekarang')
                    ->label('Stok Awal')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.001)
                    ->default(0)
                    ->required()
                    ->disabled(fn (?object $record): bool => $record !== null)
                    ->dehydrated(fn (?object $record): bool => $record === null),

                TextInput::make('batas_minimum')
                    ->label('Batas Minimum Stok')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.001)
                    ->default(0)
                    ->required(),

                TextInput::make('harga_sekarang')
                    ->label('Harga per Satuan (Rp)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->default(0)
                    ->required(),
            ]);
    }
}
