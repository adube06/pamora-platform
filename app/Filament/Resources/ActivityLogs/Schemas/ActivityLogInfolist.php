<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('action'),
                TextEntry::make('description'),
                TextEntry::make('occasion.title')
                    ->label('Occasion')
                    ->default('—'),
                TextEntry::make('user.name')
                    ->label('Actor')
                    ->default('System'),
                TextEntry::make('subject_type'),
                TextEntry::make('subject_id'),
            ]);
    }
}
