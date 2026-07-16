<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_admin')
                    ->label('Platform Administrator'),
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
