<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VendorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('business_name'),
                TextEntry::make('owner.name')
                    ->label('Owner'),
                TextEntry::make('owner.email')
                    ->label('Owner email'),
                TextEntry::make('categories')
                    ->badge(),
                TextEntry::make('service_areas')
                    ->badge()
                    ->default('—'),
                TextEntry::make('contact_email'),
                TextEntry::make('contact_phone'),
                TextEntry::make('verification_status')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
