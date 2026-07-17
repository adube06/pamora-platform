<?php

namespace App\Domains\Marketplace\Domain\Models;

use App\Domains\Marketplace\Domain\Enums\BookingStatus;
use App\Domains\Marketplace\Domain\Enums\VendorStatus;
use App\Domains\Marketplace\Domain\Enums\VendorVerificationStatus;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Carbon\CarbonInterface;
use Database\Factories\VendorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function rentalItems(): HasMany
    {
        return $this->hasMany(RentalItem::class);
    }

    public function availabilityBlocks(): HasMany
    {
        return $this->hasMany(AvailabilityBlock::class);
    }

    /**
     * Whether this Vendor can take on new work on the given date — checked
     * fresh every call (ADR-004: derived state is never stored). Blocked by
     * either a manual/maintenance AvailabilityBlock, or an existing Booking
     * against any of the Vendor's Services on that date.
     */
    public function isAvailableOn(CarbonInterface $date): bool
    {
        $blocked = $this->availabilityBlocks()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();

        if ($blocked) {
            return false;
        }

        return ! Booking::whereIn('service_id', $this->services()->pluck('id'))
            ->whereIn('status', [BookingStatus::Confirmed, BookingStatus::InProgress, BookingStatus::Completed])
            ->whereHas('occasion', fn ($query) => $query->whereDate('primary_date', $date))
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
