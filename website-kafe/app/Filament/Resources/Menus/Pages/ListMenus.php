<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Services\ImportMenuService;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('importMenu')
                ->label('Import Menu')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Menu')
                ->schema([
                    FileUpload::make('file')
                        ->label('File CSV Menu')
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
                    $filePath = Storage::disk('local')->path($data['file']);

                    try {
                        $hasil = app(ImportMenuService::class)->import($filePath);

                        Notification::make()
                            ->title('Import Menu Selesai')
                            ->body(
                                "Berhasil: {$hasil['berhasil']} menu. " .
                                "Dilewati: {$hasil['dilewati']} baris."
                            )
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Import Menu Gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($data['file']);
                    }
                }),

            CreateAction::make()
                ->label('Tambah Menu')
                ->icon('heroicon-o-plus'),
        ];
    }
}
