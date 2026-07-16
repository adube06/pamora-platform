<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('action')
                    ->badge()
                    ->searchable(),
                TextColumn::make('description')
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('occasion.title')
                    ->label('Occasion')
                    ->default('—'),
                TextColumn::make('user.name')
                    ->label('Actor')
                    ->default('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->options(fn () => ActivityLog::query()
                        ->distinct()
                        ->orderBy('action')
                        ->pluck('action', 'action')),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                //
            ]);
    }
}
