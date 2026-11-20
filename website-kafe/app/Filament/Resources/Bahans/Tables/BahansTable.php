<?php

namespace App\Filament\Resources\Bahans\Tables;

use App\Models\Bahan;
use App\Services\ImportBahanService;
use App\Services\SupplierHargaService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
                    ->alignCenter(),

                TextColumn::make('satuan_dasar')
                    ->label('Satuan Dasar')
                    ->badge()
                    ->alignCenter(),

                TextColumn::make('batas_minimum')
                    ->label('Batas Minimum')
                    ->formatStateUsing(fn ($state): string =>
                        rtrim(
                            rtrim(number_format((float) $state, 3, ',', '.'), '0'),
                            ','
                        )
                    )
                    ->sortable()
                    ->alignCenter(),

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
                    ->sortable()
                    ->alignCenter(),
            ])

            ->headerActions([
                Action::make('importBahan')
                    ->label('Import Bahan')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('Import Data Bahan')
                    ->modalDescription(
                        'Pilih file CSV yang berisi data bahan.'
                    )
                    ->schema([
                        FileUpload::make('file')
                            ->label('File CSV')
                            ->disk('local')
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                            ])
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = $data['file'];

                        try {
                            $lokasiFile = Storage::disk('local')->path($path);

                            $hasil = app(ImportBahanService::class)
                                ->import($lokasiFile);

                            Notification::make()
                                ->title('Import Bahan Selesai')
                                ->body(
                                    "Berhasil: {$hasil['berhasil']} data. "
                                    . "Dilewati: {$hasil['dilewati']} data."
                                )
                                ->success()
                                ->persistent()
                                ->send();

                        } catch (Throwable $e) {
                            report($e);

                            Notification::make()
                                ->title('Import Bahan Gagal')
                                ->body('Terjadi kesalahan saat memproses CSV.')
                                ->danger()
                                ->send();

                        } finally {
                            Storage::disk('local')->delete($path);
                        }
                    }),

                Action::make('sinkronkanHarga')
                    ->label('Sinkronkan Harga Supplier')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Sinkronkan Harga Supplier')
                    ->modalDescription(
                        'Sistem akan mengambil harga bahan terbaru dari layanan supplier.'
                    )
                    ->action(function () {
                        $hasil = app(SupplierHargaService::class)->sinkronkan();

                        if ($hasil['berhasil']) {
                            Notification::make()
                                ->title('Sinkronisasi Berhasil')
                                ->body(
                                    $hasil['pesan']
                                    . ' Jumlah bahan diperbarui: ' . $hasil['jumlah']
                                    . '. Tanggal data: ' . $hasil['tanggal']
                                )
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Sinkronisasi Tidak Berhasil')
                            ->body(
                                $hasil['pesan']
                                . ' Harga terakhir tetap digunakan. Tanggal pembaruan terakhir: '
                                . ($hasil['tanggal'] ?? 'Belum tersedia')
                            )
                            ->warning()
                            ->send();
                    }),
            ])

            ->recordActions([
                
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

                    EditAction::make(),

            ]);
    }
}
