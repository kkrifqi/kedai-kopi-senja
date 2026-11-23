<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Models\Menu;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
                    ->searchable(),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state): string =>
                        'Rp ' . rtrim(
                            rtrim(number_format((float) $state, 2, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable(),

                IconColumn::make('status_aktif')
                    ->label('Menu Aktif')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('status_habis')
                    ->label('Ketersediaan')
                    ->formatStateUsing(fn ($state) => $state ? 'Habis' : 'Tersedia')
                    ->color(fn ($state) => $state ? 'danger' : 'success')
                    ->weight('bold')
                    ->badge()
                    ->alignCenter(),
            ])

            ->headerActions([
                
            ])
          
            ->recordActions([
                EditAction::make(),

                Action::make('hapusMenu')
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Menu')
                    ->modalDescription('Apakah kamu yakin ingin menghapus menu ini? Tindakan ini tidak dapat dibatalkan.')
                    ->modalSubmitActionLabel('Ya, Hapus Menu')
                    ->action(function (Menu $record) {
                        if ($record->reseps()->exists()) {
                            Notification::make()
                                ->title('Menu Tidak Bisa Dihapus')
                                ->body('Menu ini masih memiliki resep. Hapus seluruh resepnya terlebih dahulu melalui halaman Kelola Resep.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->delete();

                        Notification::make()
                            ->title('Menu Berhasil Dihapus')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}