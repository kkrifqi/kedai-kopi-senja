<?php

namespace App\Filament\Resources\Bahans\Tables;

use App\Models\Bahan;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Bahan')
                    ->searchable(),

                TextColumn::make('stok_sekarang')
                    ->label('Stok Sekarang')
                    ->formatStateUsing(fn ($state): string =>
                        rtrim(
                            rtrim(number_format((float) $state, 3, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('satuan_dasar')
                    ->label('Satuan Dasar')
                    ->badge(),

                TextColumn::make('batas_minimum')
                    ->label('Batas Minimum')
                    ->formatStateUsing(fn ($state): string =>
                        rtrim(
                            rtrim(number_format((float) $state, 3, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable(),

                TextColumn::make('harga_sekarang')
                    ->label('Harga Sekarang')
                    ->formatStateUsing(fn ($state): string =>
                        'Rp ' . rtrim(
                            rtrim(number_format((float) $state, 2, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable(),

                TextColumn::make('tanggal_update_harga')
                    ->label('Tanggal Update Harga')
                    ->date('d/m/Y')
                    ->placeholder('Belum diperbarui')
                    ->sortable()
                    ->alignCenter(),
            ])

            ->recordActions([
                EditAction::make(),
                
                DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Bahan')
                    ->modalDescription(
                        'Konfirmasi hapus bahan ini?'
                    )
                    ->before(function (DeleteAction $action, Bahan $record) {
                        if ($record->reseps()->exists()) {
                            Notification::make()
                                ->title('Bahan Tidak Bisa Dihapus')
                                ->body(
                                    'Bahan ini masih digunakan dalam resep. '
                                    . 'Hapus atau ubah resep yang menggunakannya terlebih dahulu.'
                                )
                                ->warning()
                                ->send();

                            $action->cancel();
                        }
                    }),


            ]);
    }
}
