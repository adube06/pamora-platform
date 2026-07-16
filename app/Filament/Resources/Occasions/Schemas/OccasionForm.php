<?php

namespace App\Filament\Resources\Occasions\Schemas;

use App\Domains\Occasion\Domain\Enums\OccasionStatus;
use App\Domains\Occasion\Domain\Enums\OccasionType;
use App\Domains\Occasion\Domain\Enums\OccasionVisibility;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OccasionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(collect(OccasionType::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->required(),
                Select::make('visibility')
                    ->options(collect(OccasionVisibility::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->required(),
                Select::make('status')
                    ->options(collect(OccasionStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                    ->helperText('Changing this is a support/compliance action — it is audit logged.')
                    ->required(),
            ]);
    }
}
