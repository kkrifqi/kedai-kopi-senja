<?php

namespace App\Filament\Resources\StokMasuks;

use App\Filament\Resources\StokMasuks\Pages\CreateStokMasuk;
use App\Filament\Resources\StokMasuks\Pages\ListStokMasuks;
use App\Filament\Resources\StokMasuks\Schemas\StokMasukForm;
use App\Filament\Resources\StokMasuks\Tables\StokMasuksTable;
use App\Models\StokMasuk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class StokMasukResource extends Resource
{
    protected static ?string $model = StokMasuk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'tanggal_masuk';

    protected static ?string $navigationLabel = 'Stok Masuk';

    protected static ?string $pluralModelLabel = 'Stok Masuk';

    public static function form(Schema $schema): Schema
    {
        return StokMasukForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StokMasuksTable::configure($table);
    }

    public static function getRecordTitle(?\Illuminate\Database\Eloquent\Model $record): ?string
    {
        return 'Detail Stok Masuk';
    }
    public static function getPages(): array
    {
        return [
            'index' => ListStokMasuks::route('/'),
            'create' => CreateStokMasuk::route('/create'),
        ];
    }
}
