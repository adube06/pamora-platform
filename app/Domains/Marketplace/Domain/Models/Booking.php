<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): BookingFactory
    {
        return BookingFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'service_id',
        'quotation_id',
        'confirmed_by',
        'status',
        'agreed_price',
        'currency',
        'notes',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'agreed_price' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
