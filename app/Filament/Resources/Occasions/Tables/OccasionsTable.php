<?php

namespace App\Filament\Resources\Occasions\Tables;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OccasionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('host.name')
                    ->label('Host')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label()),
                TextColumn::make('primary_date')
                    ->date(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(OccasionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
