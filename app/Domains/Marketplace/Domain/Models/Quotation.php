<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\QuotationStatus;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\QuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): QuotationFactory
    {
        return QuotationFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'service_id',
        'requested_by',
        'message',
        'status',
        'quoted_price',
        'currency',
        'vendor_notes',
        'requested_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuotationStatus::class,
            'quoted_price' => 'decimal:2',
            'requested_at' => 'datetime',
            'responded_at' => 'datetime',
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
