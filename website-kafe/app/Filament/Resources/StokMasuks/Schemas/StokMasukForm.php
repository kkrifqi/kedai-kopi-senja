<?php

namespace App\Filament\Resources\StokMasuks\Schemas;

use App\Models\Bahan;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class StokMasukForm
{
    public static function configure(
        Schema $schema,
        bool $readOnly = false
    ): Schema {
        $repeater = Repeater::make(
            $readOnly ? 'detailStokMasuks' : 'details'
        )
            ->label('Detail Bahan Masuk')
            ->schema([
                Select::make('bahan_id')
                    ->label('Bahan')
                    ->options(
                        fn () => Bahan::query()
                            ->orderBy('nama')
                            ->pluck('nama', 'id')
                            ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->disabled($readOnly),

                TextInput::make('jumlah')
                    ->label('Jumlah Masuk')
                    ->numeric()
                    ->minValue(0.001)
                    ->step(0.001)
                    ->rules(['decimal:0,3'])
                    ->suffix(
                        fn (Get $get): ?string => Bahan::find(
                            $get('bahan_id')
                        )?->satuan_dasar
                    )
                    ->required()
                    ->disabled($readOnly),
            ])
            ->columns(2)
            ->minItems(1)
            ->addActionLabel('Tambah Bahan')
            ->reorderable(! $readOnly)
            ->addable(! $readOnly)
            ->deletable(! $readOnly)
            ->required();

        // Saat read-only, ambil detail dari relasi StokMasuk.
        if ($readOnly) {
            $repeater->relationship('detailStokMasuks');
        }

        return $schema->components([
            DatePicker::make('tanggal_masuk')
                ->label('Tanggal Masuk')
                ->default(now())
                ->displayFormat('d F Y')
                ->required()
                ->native(false)
                ->disabled($readOnly),

            $repeater,
        ]);
    }
}
