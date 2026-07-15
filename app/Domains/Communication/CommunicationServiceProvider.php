<?php

namespace App\Domains\Communication;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publishing is checked against the Occasion, not an existing
        // Announcement — same pattern as create-task/manage-checklist.
        // Viewing reuses OccasionPolicy::view() (any active OccasionMember)
        // since Announcement visibility is a transparency default, not a
        // separate permission (Product Philosophy Principle 6).
        Gate::define('publish-announcement', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::CommunicationPublishAnnouncement) ?? false;
        });

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
