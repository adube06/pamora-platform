<?php

namespace App\Domains\Planning;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Presentation\Policies\TaskPolicy;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PlanningServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Task::class, TaskPolicy::class);

        // "Can this user create a Task on this Occasion" is checked against
        // the Occasion, not an existing Task — same pattern as People's
        // invite-member/remove-member abilities.
        Gate::define('create-task', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::PlanningCreateTask) ?? false;
        });

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
