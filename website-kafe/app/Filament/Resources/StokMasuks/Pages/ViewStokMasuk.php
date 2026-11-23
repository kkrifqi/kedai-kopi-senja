<?php

namespace App\Filament\Resources\StokMasuks\Pages;

use App\Filament\Resources\StokMasuks\StokMasukResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewStokMasuk extends ViewRecord
{
    protected static string $resource = StokMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(StokMasukResource::getUrl('index')),
        ];
    }
}
