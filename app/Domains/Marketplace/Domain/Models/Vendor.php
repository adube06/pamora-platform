<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\VendorStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): VendorFactory
    {
        return VendorFactory::new();
    }

    protected $fillable = [
        'owner_id',
        'business_name',
        'categories',
        'service_areas',
        'contact_email',
        'contact_phone',
        'verification_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'service_areas' => 'array',
            'verification_status' => VendorVerificationStatus::class,
            'status' => VendorStatus::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
