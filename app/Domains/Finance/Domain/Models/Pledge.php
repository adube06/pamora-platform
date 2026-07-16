<?php

namespace App\Domains\Finance\Domain\Models;

use App\Domains\Finance\Domain\Enums\PledgeStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\PledgeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pledge extends Model
{
    use HasFactory, HasUuid;

    protected static function newFactory(): PledgeFactory
    {
        return PledgeFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'pledgor_name',
        'pledgor_phone',
        'amount',
        'currency',
        'status',
        'message',
        'recorded_by',
        'pledged_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PledgeStatus::class,
            'amount' => 'decimal:2',
            'pledged_at' => 'date',
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
