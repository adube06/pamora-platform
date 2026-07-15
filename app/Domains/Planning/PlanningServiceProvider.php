<?php

namespace App\Domains\Planning;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Events\TaskCompleted;
use App\Domains\Planning\Domain\Models\Task;
use App\Domains\Planning\Infrastructure\Listeners\MilestoneAchievementListener;
use App\Domains\Planning\Presentation\Policies\TaskPolicy;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Event;
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

        // Occasion-scoped, used only as a UI hint on the Planning index page
        // (whether to render a Complete/Reopen button at all) — the real
        // enforcement on the mutating endpoints is TaskPolicy::complete()/
        // reopen(), same split as create-task vs StoreTaskRequest.
        Gate::define('complete-task', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::PlanningCompleteTask) ?? false;
        });

        Gate::define('reopen-task', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::PlanningReopenTask) ?? false;
        });

        // Checklist creation isn't tied to an existing Checklist instance,
        // same reasoning as create-task.
        Gate::define('manage-checklist', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::PlanningManageChecklist) ?? false;
        });

        Gate::define('manage-milestone', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::PlanningManageMilestone) ?? false;
        });

        // BR-017 achievement detection — Planning's own internal
        // cross-cutting concern, same Event::listen pattern Shared uses
        // for audit logging (ADR-006), not logic inlined into
        // CompleteTaskService.
        Event::listen(TaskCompleted::class, [MilestoneAchievementListener::class, 'handle']);

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
