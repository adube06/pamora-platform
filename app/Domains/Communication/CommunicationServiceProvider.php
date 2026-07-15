<?php

namespace App\Domains\Communication;

use App\Domains\Communication\Domain\Models\Notification;
use App\Domains\Communication\Infrastructure\Listeners\NotificationSubscriber;
use App\Domains\Communication\Presentation\Policies\NotificationPolicy;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Planning\Domain\Events\TaskAssigned;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CommunicationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Notification::class, NotificationPolicy::class);

        // Publishing is checked against the Occasion, not an existing
        // Announcement — same pattern as create-task/manage-checklist.
        // Viewing reuses OccasionPolicy::view() (any active OccasionMember)
        // since Announcement visibility is a transparency default, not a
        // separate permission (Product Philosophy Principle 6).
        Gate::define('publish-announcement', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::CommunicationPublishAnnouncement) ?? false;
        });

        // FR-002 / ADR-006 — Notifications are generated from Domain
        // Events, never called directly by the originating domain. A
        // distinct concern from AuditLogSubscriber (recipient-facing,
        // not compliance-facing), even though both react to TaskAssigned.
        Event::listen(TaskAssigned::class, [NotificationSubscriber::class, 'handleTaskAssigned']);

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
