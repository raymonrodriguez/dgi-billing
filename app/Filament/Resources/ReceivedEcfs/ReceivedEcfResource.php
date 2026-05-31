<?php

namespace App\Filament\Resources\ReceivedEcfs;

use App\Filament\Resources\ReceivedEcfs\Pages\CreateReceivedEcf;
use App\Filament\Resources\ReceivedEcfs\Pages\EditReceivedEcf;
use App\Filament\Resources\ReceivedEcfs\Pages\ListReceivedEcfs;
use App\Filament\Resources\ReceivedEcfs\Schemas\ReceivedEcfForm;
use App\Filament\Resources\ReceivedEcfs\Tables\ReceivedEcfsTable;
use App\Models\ReceivedEcf;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReceivedEcfResource extends Resource
{
    protected static ?string $model = ReceivedEcf::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static string|UnitEnum|null $navigationGroup = 'Recepción';


    public static function form(Schema $schema): Schema
    {
        return ReceivedEcfForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceivedEcfsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceivedEcfs::route('/'),
            'create' => CreateReceivedEcf::route('/create'),
            'edit' => EditReceivedEcf::route('/{record}/edit'),
        ];
    }
}
