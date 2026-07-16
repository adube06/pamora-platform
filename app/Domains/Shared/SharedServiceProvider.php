<?php

namespace App\Domains\Shared;

use App\Domains\Shared\Infrastructure\ActivityLog\AuditLogSubscriber;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::subscribe(AuditLogSubscriber::class);

        // Platform Administrators (Admin Portal) bypass every Occasion-
        // scoped Policy/Gate check — the Permission Catalog's own
        // "Platform-Scoped" section frames this as independent of any
        // single Occasion's membership, e.g. admin.manage_occasions:
        // "View/manage any Occasion for support or compliance purposes."
        Gate::before(fn (User $user) => $user->is_admin ? true : null);
    }
}
