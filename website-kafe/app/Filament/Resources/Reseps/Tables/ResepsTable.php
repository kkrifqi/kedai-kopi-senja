<?php

namespace App\Filament\Resources\Reseps\Tables;

use App\Models\Menu;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResepsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) =>
                $query->with('reseps.bahan')
            )
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Menu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reseps')
                    ->label('Detail Bahan Resep')
                    ->state(fn (Menu $record) =>
                        $record->reseps
                            ->map(fn ($resep) =>
                                $resep->bahan->nama
                                . ' — '
                                . rtrim(rtrim(
                                    number_format(
                                        (float) $resep->takaran,
                                        3,
                                        ',',
                                        '.'
                                    ),
                                    '0'
                                ), ',')
                                . ' '
                                . $resep->bahan->satuan_dasar
                            )
                            ->implode("\n")
                    )
                    ->wrap(),

                TextColumn::make('reseps_count')
                    ->counts('reseps')
                    ->label('Jumlah Bahan'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Kelola Resep'),

                Action::make('hapusResep')
                    ->label('Hapus Resep')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Semua Resep')
                    ->modalDescription('Apakah kamu yakin ingin menghapus seluruh bahan resep dari menu ini? Data menu tidak akan ikut terhapus.')
                    ->modalSubmitActionLabel('Ya, Hapus Resep')
                    ->action(function (Menu $record) {
                        $jumlah = $record->reseps()->count();

                        if ($jumlah === 0) {
                            Notification::make()
                                ->title('Resep Kosong')
                                ->body('Menu ini belum memiliki resep.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->reseps()->delete();

                        Notification::make()
                            ->title('Resep Berhasil Dihapus')
                            ->body("Sebanyak {$jumlah} bahan resep berhasil dihapus. Data menu tetap tersimpan.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
