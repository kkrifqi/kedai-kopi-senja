<?php

namespace App\Filament\Resources\StokMasuks\Pages;

use App\Filament\Resources\StokMasuks\StokMasukResource;
use App\Models\StokMasuk;
use App\Services\StokMasukService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateStokMasuk extends CreateRecord
{
    protected static string $resource = StokMasukResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(StokMasukService::class)->create(
            $data,
            (int) auth()->id(),
        );
    }
}
