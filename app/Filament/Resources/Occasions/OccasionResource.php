<?php

namespace App\Filament\Resources\Occasions;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Filament\Resources\Occasions\Pages\EditOccasion;
use App\Filament\Resources\Occasions\Pages\ListOccasions;
use App\Filament\Resources\Occasions\Pages\ViewOccasion;
use App\Filament\Resources\Occasions\Schemas\OccasionForm;
use App\Filament\Resources\Occasions\Schemas\OccasionInfolist;
use App\Filament\Resources\Occasions\Tables\OccasionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OccasionResource extends Resource
{
    protected static ?string $model = Occasion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Occasion Management';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return OccasionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OccasionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OccasionsTable::configure($table);
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
            'index' => ListOccasions::route('/'),
            'view' => ViewOccasion::route('/{record}'),
            'edit' => EditOccasion::route('/{record}/edit'),
        ];
    }
}
