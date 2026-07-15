<?php

namespace App\Domains\Shared;

use App\Domains\Shared\Infrastructure\ActivityLog\AuditLogSubscriber;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::subscribe(AuditLogSubscriber::class);
    }
}
