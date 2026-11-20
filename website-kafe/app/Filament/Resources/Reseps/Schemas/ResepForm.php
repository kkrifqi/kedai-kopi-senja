<?php

namespace App\Filament\Resources\Reseps\Schemas;

use App\Models\Bahan;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ResepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('nama')
                ->label('Nama Menu')
                ->disabled()
                ->dehydrated(false),

            Repeater::make('reseps')
                ->label('Daftar Bahan Resep')
                ->relationship()
                ->schema([
                    Select::make('bahan_id')
                        ->label('Bahan')
                        ->relationship('bahan', 'nama')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    TextInput::make('takaran')
                        ->label('Takaran per Porsi')
                        ->numeric()
                        ->minValue(0.001)
                        ->step(0.001)
                        ->required()
                        ->suffix(fn (Get $get): ?string =>
                            Bahan::query()
                                ->whereKey($get('bahan_id'))
                                ->value('satuan_dasar')
                        ),
                ])
                ->columns(2)
                ->defaultItems(1)
                ->minItems(1)
                ->addActionLabel('Tambah Bahan')
                ->reorderable(false)
                ->required(),
        ]);
    }
}
