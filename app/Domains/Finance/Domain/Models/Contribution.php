<?php

namespace App\Domains\Finance\Domain\Models;

use App\Domains\Finance\Domain\Enums\ContributionMethod;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\ContributionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): ContributionFactory
    {
        return ContributionFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'contributor_name',
        'contributor_phone',
        'amount',
        'currency',
        'method',
        'message',
        'recorded_by',
        'contributed_at',
    ];

    protected function casts(): array
    {
        return [
            'method' => ContributionMethod::class,
            'amount' => 'decimal:2',
            'contributed_at' => 'date',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
