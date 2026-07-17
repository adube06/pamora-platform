<?php

namespace App\Filament\Resources\Vendors;

use App\Domains\Marketplace\Domain\Models\Vendor;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Filament\Resources\Vendors\Pages\ViewVendor;
use App\Filament\Resources\Vendors\Schemas\VendorInfolist;
use App\Filament\Resources\Vendors\Tables\VendorsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Vendors are created via the public "apply to become a Vendor" flow
 * (App\Domains\Marketplace\Application\Services\ApplyAsVendorService),
 * never by an admin directly — no Create/Edit pages registered, same
 * reasoning as ActivityLogResource.
 */
class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketplace';

    public static function infolist(Schema $schema): Schema
    {
        return VendorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VendorsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVendors::route('/'),
            'view' => ViewVendor::route('/{record}'),
        ];
    }
}
