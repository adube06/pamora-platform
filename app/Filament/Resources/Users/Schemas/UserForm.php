<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->confirmed()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->visibleOn('create'),
                TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->visibleOn('create'),
                Toggle::make('is_admin')
                    ->label('Platform Administrator')
                    ->helperText('Grants access to this Admin Portal.'),
            ]);
    }
}
