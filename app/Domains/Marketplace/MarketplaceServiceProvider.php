<?php

namespace App\Domains\Marketplace;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MarketplaceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // No Gate::policy()/Gate::define() here — Marketplace is not
        // Occasion-scoped (the first domain in this app where that's
        // true), so there's no OccasionMember::hasPermission() to check.
        // Vendor-profile ownership is a plain equality check inline in
        // VendorController, and applying is gated by auth middleware
        // alone — same reasoning as Communication's manage_preferences
        // (Self-scoped, no Permission enum case).
        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
