<?php

namespace App\Domains\Occasion;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Occasion\Presentation\Policies\OccasionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OccasionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Occasion::class, OccasionPolicy::class);

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
