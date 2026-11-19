<?php

namespace App\Filament\Resources\Bahans\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Services\SupplierHargaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class BahansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode')
                    ->label('Kode Bahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Nama Bahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stok_sekarang')
                    ->label('Stok Sekarang')
                    ->formatStateUsing(fn ($state): string =>
                        rtrim(
                            rtrim(number_format((float) $state, 3, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable(),

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
                        'Rp ' . number_format((float) $state, 2, ',', '.')
                    )
                    ->sortable(),

                TextColumn::make('tanggal_update_harga')
                    ->label('Tanggal Update Harga')
                    ->date('d/m/Y')
                    ->placeholder('Belum diperbarui')
                    ->sortable(),
            ])

            ->headerActions([
                Action::make('sinkronkanHarga')
                    ->label('Sinkronkan Harga Supplier')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronkan Harga Supplier')
                    ->modalDescription(
                        'Sistem akan mengambil harga bahan terbaru dari layanan supplier. Harga yang tersimpan akan diperbarui berdasarkan kode bahan.'
                    )
                    ->action(function () {
                        $hasil = app(SupplierHargaService::class)->sinkronkan();

                        if ($hasil['berhasil']) {
                            Notification::make()
                                ->title('Sinkronisasi Berhasil')
                                ->body(
                                    $hasil['pesan'] .
                                    ' Jumlah bahan diperbarui: ' . $hasil['jumlah'] .
                                    '. Tanggal data: ' . $hasil['tanggal']
                                )
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Sinkronisasi Tidak Berhasil')
                            ->body(
                                $hasil['pesan'] .
                                ' Harga terakhir tetap digunakan. Tanggal pembaruan terakhir: ' .
                                ($hasil['tanggal'] ?? 'Belum tersedia')
                            )
                            ->warning()
                            ->send();
                    }),
            ])

            ->recordActions([
                EditAction::make(),
            ]);
    }
}
