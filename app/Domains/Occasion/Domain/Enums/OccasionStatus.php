<?php

namespace App\Domains\Occasion\Domain\Enums;

enum OccasionStatus: string
{
    case Draft = 'draft';
    case Planning = 'planning';
    case Active = 'active';
    case Completed = 'completed';
    case Archived = 'archived';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * @return array<int, self>
     */
    private function allowedNextStatuses(): array
    {
        return match ($this) {
            self::Draft => [self::Planning, self::Cancelled],
            self::Planning => [self::Active, self::Cancelled],
            self::Active => [self::Completed, self::Cancelled],
            self::Completed => [self::Archived],
            self::Archived, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedNextStatuses(), true);
    }
}
