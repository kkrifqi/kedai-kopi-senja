<?php

namespace App\Filament\Resources\Reseps;

use App\Filament\Resources\Reseps\Pages\CreateResep;
use App\Filament\Resources\Reseps\Pages\EditResep;
use App\Filament\Resources\Reseps\Pages\ListReseps;
use App\Filament\Resources\Reseps\Schemas\ResepForm;
use App\Filament\Resources\Reseps\Tables\ResepsTable;
use App\Models\Menu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResepResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static ?string $recordTitleAttribute = 'nama';

    protected static ?string $pluralModelLabel = 'Resep';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Resep';

   public static function form(Schema $schema): Schema
    {
        return ResepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReseps::route('/'),
            'create' => CreateResep::route('/create'),
            'edit' => EditResep::route('/{record}/edit'),
        ];
    }
}
