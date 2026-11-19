<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Menu')
                    ->required()
                    ->maxLength(100)
                    ->unique(ignoreRecord: true),

                TextInput::make('harga_jual')
                    ->label('Harga Jual (Rp)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),

                Toggle::make('status_aktif')
                    ->label('Menu Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}