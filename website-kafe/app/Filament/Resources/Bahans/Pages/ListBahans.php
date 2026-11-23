<?php

namespace App\Filament\Resources\Bahans\Pages;

use App\Filament\Resources\Bahans\BahanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Services\ImportBahanService;
use App\Services\SupplierHargaService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListBahans extends ListRecords
{
    protected static string $resource = BahanResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('importBahan')
                ->label('Import Bahan')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Bahan')
                ->schema([
                    FileUpload::make('file')
                        ->label('File CSV Bahan')
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

            CreateAction::make()
            ->label('Tambah Bahan')
            ->icon('heroicon-o-plus'),
        ];
    }
}
