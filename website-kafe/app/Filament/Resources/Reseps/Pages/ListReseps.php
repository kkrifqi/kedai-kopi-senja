<?php

namespace App\Filament\Resources\Reseps\Pages;

use App\Filament\Resources\Reseps\ResepResource;
use App\Services\ImportResepService;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListReseps extends ListRecords
{
    protected static string $resource = ResepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importResep')
                ->label('Import Resep')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Resep')
                ->schema([
                    FileUpload::make('file')
                        ->label('File CSV Resep')
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/vnd.ms-excel',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $file = $data['file'];

                    try {
                        $path = Storage::disk('local')->path($file);

                        $hasil = app(ImportResepService::class)
                            ->import($path);

                        $notifikasi = Notification::make()
                            ->title('Import Resep Selesai')
                            ->body(
                                "Berhasil: {$hasil['berhasil']} resep.\n" .
                                "Dilewati: {$hasil['dilewati']} baris.\n\n" 
                            );

                        if ($hasil['dilewati'] > 0) {
                            $notifikasi->warning();
                        } else {
                            $notifikasi->success();
                        }

                        $notifikasi->send();

                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Resep Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($file);
                    }
                }),

            CreateAction::make()
                ->label('Tambah Resep')
                ->icon('heroicon-o-plus'),
        ];
    }
}
