<?php

namespace App\Domains\Marketplace;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Vendor/Service ownership checks are plain equality checks inline
        // in their own Requests/Controllers — Marketplace is Platform-scoped
        // there (the first domain in this app where that's true), so there's
        // no OccasionMember::hasPermission() to check. Requesting a
        // Quotation is different: the catalog scopes it to the Occasion
        // (Host), so this is Marketplace's first genuinely Occasion-scoped
        // Gate — same shape as Media's upload-media gate, since the entity
        // being created (Quotation) belongs to Marketplace, not Occasion.
        Gate::define('request-quotation', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::MarketplaceRequestQuotation) ?? false;
        });

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
