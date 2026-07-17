<?php

namespace App\Domains\People;

use App\Domains\People\Presentation\Policies\OccasionMemberPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PeopleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Named abilities rather than a class-bound Policy: the argument is
        // an Occasion, but the "who can invite/remove members" rule is
        // owned by the People domain, not the Occasion domain (Constitution
        // Article V). Occasion already has its own bound OccasionPolicy.
        Gate::define('invite-member', [OccasionMemberPolicy::class, 'invite']);
        Gate::define('remove-member', [OccasionMemberPolicy::class, 'remove']);
        Gate::define('reopen-rsvp', [OccasionMemberPolicy::class, 'reopenRsvp']);
        Gate::define('assign-responsibilities', [OccasionMemberPolicy::class, 'assignResponsibilities']);

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
