<?php

namespace App\Filament\Resources\Occasions\Pages;

use App\Filament\Resources\Occasions\OccasionResource;
use Filament\Resources\Pages\ListRecords;

class ListOccasions extends ListRecords
{
    protected static string $resource = OccasionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
