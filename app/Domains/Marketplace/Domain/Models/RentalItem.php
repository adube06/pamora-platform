<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\RentalItemStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use Database\Factories\RentalItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalItem extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): RentalItemFactory
    {
        return RentalItemFactory::new();
    }

    protected $fillable = [
        'vendor_id',
        'name',
        'description',
        'quantity_available',
        'unit_price',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => RentalItemStatus::class,
            'unit_price' => 'decimal:2',
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
