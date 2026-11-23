<?php

namespace App\Filament\Resources\Reseps\Tables;

use App\Models\Menu;
use App\Filament\Resources\Reseps\Schemas\ResepForm;
use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class ResepsTable
{
    public static function configure(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(fn ($query) =>
            $query
                ->whereHas('reseps')
                ->with('reseps.bahan')
)
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Menu')
                    ->searchable(),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->sortable(),

                TextColumn::make('reseps_count')
                    ->counts('reseps')
                    ->label('Jumlah Bahan')
                    ->badge()
                    ->alignCenter(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->schema(fn (Schema $schema) =>
                        ResepForm::configure($schema, readOnly: true)->getComponents()
                    ),   


                EditAction::make()
                    ->label('Kelola Resep')
                    ->icon('heroicon-o-pencil-square'),

                Action::make('hapusResep')
                    ->label('Hapus Resep')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Semua Resep')
                    ->modalDescription(
                        'Apakah kamu yakin ingin menghapus seluruh bahan resep dari menu ini? Data menu tidak akan ikut terhapus.'
                    )
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
                            ->body(
                                "Sebanyak {$jumlah} bahan resep berhasil dihapus. Data menu tetap tersimpan."
                            )
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
