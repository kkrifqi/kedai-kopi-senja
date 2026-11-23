<?php

namespace App\Filament\Resources\Reseps\Schemas;

use App\Models\Bahan;
use App\Models\Menu;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ResepForm
{
    public static function configure(
        Schema $schema,
        bool $readOnly = false
    ): Schema {
        $repeater = Repeater::make('reseps')
            ->label('Daftar Bahan Resep')
            ->relationship()
            ->schema([
                Select::make('bahan_id')
                    ->label('Bahan')
                    ->relationship('bahan', 'nama')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->disabled($readOnly),

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
                    )
                    ->disabled($readOnly),
            ])
            ->columns(2)
            ->defaultItems(1)
            ->minItems(1)
            ->addActionLabel('Tambah Bahan')
            ->reorderable(! $readOnly)
            ->addable(! $readOnly)
            ->deletable(! $readOnly)
            ->required();
        
        return $schema->components([
            Select::make('menu_id')
                ->label('Pilih Menu')
                ->options(fn () => Menu::query()
                    ->whereDoesntHave('reseps')
                    ->orderBy('nama')
                    ->pluck('nama', 'id')
                )
                ->searchable()
                ->preload()
                ->required()
                ->visible(fn (string $operation): bool => $operation === 'create'),

            TextInput::make('nama')
                ->label('Nama Menu')
                ->disabled()
                ->dehydrated(false)
                ->visible(fn (string $operation): bool => $operation !== 'create'),

            $repeater,
        ]);
    }
}