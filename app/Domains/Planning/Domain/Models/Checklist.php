<?php

namespace App\Domains\Planning\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\ChecklistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Checklist extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): ChecklistFactory
    {
        return ChecklistFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'name',
        'created_by',
    ];

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
