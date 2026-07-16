<?php

namespace App\Domains\Finance;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FinanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Recording a Contribution is checked against the Occasion, not an
        // existing Contribution — same pattern as create-task/invite-member.
        // Viewing reuses OccasionPolicy::view() (any active OccasionMember)
        // since Contribution visibility is a transparency default, not a
        // separate permission (Product Philosophy Principle 6).
        Gate::define('record-contribution', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::FinanceRecordContribution) ?? false;
        });

        // Budget visibility is permission-gated in the Permission Catalog
        // (unlike Contributions, which use the transparency default above),
        // so view-budget checks a real permission rather than membership.
        Gate::define('view-budget', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::FinanceViewBudget) ?? false;
        });

        Gate::define('edit-budget', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::FinanceEditBudget) ?? false;
        });

        Gate::define('record-expense', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::FinanceRecordExpense) ?? false;
        });

        // Pledges are transparency-open like Contributions (not gated
        // behind finance.view_budget) — only recording/updating them is
        // permission-gated.
        Gate::define('record-pledge', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::FinanceRecordPledge) ?? false;
        });

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
