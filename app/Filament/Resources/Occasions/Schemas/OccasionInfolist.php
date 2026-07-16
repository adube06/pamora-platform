<?php

namespace App\Filament\Resources\Occasions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OccasionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('host.name')
                    ->label('Host'),
                TextEntry::make('type')
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextEntry::make('visibility')
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextEntry::make('status')
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextEntry::make('primary_date')
                    ->date(),
                TextEntry::make('location'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
