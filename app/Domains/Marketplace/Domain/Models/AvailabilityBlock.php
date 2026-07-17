<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Shared\Domain\Concerns\HasUuid;
use Database\Factories\AvailabilityBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AvailabilityBlock extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): AvailabilityBlockFactory
    {
        return AvailabilityBlockFactory::new();
    }

    protected $fillable = [
        'vendor_id',
        'start_date',
        'end_date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
