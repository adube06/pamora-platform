<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domains\Shared\Infrastructure\ActivityLog\ActivityLog;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    /**
     * Filament CRUD saves don't go through the Service+Event pipeline
     * used everywhere else (ADR-006) — a deliberate, documented
     * exception for this first Admin Portal slice (see plan Design
     * Decision 4). Logged directly rather than via a Domain Event.
     */
    protected function afterSave(): void
    {
        ActivityLog::create([
            'occasion_id' => null,
            'user_id' => auth()->id(),
            'subject_type' => 'User',
            'subject_id' => $this->record->id,
            'action' => 'admin.user_updated',
            'description' => auth()->user()->name." updated user \"{$this->record->name}\".",
        ]);
    }
}
