<?php

namespace App\Domains\Integrations;

use App\Domains\Integrations\Domain\Contracts\EmailProvider;
use App\Domains\Integrations\Infrastructure\Providers\LaravelMailProvider;
use Illuminate\Support\ServiceProvider;

class IntegrationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmailProvider::class, LaravelMailProvider::class);
    }
}
