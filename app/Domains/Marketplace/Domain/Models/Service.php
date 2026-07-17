<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\PricingModel;
use App\Domains\Marketplace\Domain\Enums\ServiceStatus;
use App\Domains\Marketplace\Domain\Enums\VendorCategory;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    protected $fillable = [
        'vendor_id',
        'category',
        'name',
        'description',
        'pricing_model',
        'price',
        'currency',
        'estimated_duration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category' => VendorCategory::class,
            'pricing_model' => PricingModel::class,
            'status' => ServiceStatus::class,
            'price' => 'decimal:2',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
