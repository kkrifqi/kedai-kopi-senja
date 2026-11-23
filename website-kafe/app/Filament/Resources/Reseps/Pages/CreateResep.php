<?php

namespace App\Filament\Resources\Reseps\Pages;

use App\Filament\Resources\Reseps\ResepResource;
use App\Models\Menu;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateResep extends CreateRecord
{
    protected static string $resource = ResepResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return Menu::query()
            ->whereKey($data['menu_id'])
            ->whereDoesntHave('reseps')
            ->firstOrFail();
    }
}
