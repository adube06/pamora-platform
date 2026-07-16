<?php

namespace App\Filament\Resources\Occasions\Pages;

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Filament\Resources\Occasions\OccasionResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOccasion extends EditRecord
{
    protected static string $resource = OccasionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * Same documented ADR-006 exception as EditUser::afterSave() —
     * see plan Design Decision 4.
     */
    protected function afterSave(): void
    {
        ActivityLog::create([
            'occasion_id' => $this->record->id,
            'user_id' => auth()->id(),
            'subject_type' => 'Occasion',
            'subject_id' => $this->record->id,
            'action' => 'admin.occasion_updated',
            'description' => auth()->user()->name." updated Occasion \"{$this->record->title}\".",
        ]);
    }
}
