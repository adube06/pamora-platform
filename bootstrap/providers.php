<?php

use App\Domains\Communication\CommunicationServiceProvider;
use App\Domains\Finance\FinanceServiceProvider;
use App\Domains\Identity\IdentityServiceProvider;
use App\Domains\Media\MediaServiceProvider;
use App\Domains\Occasion\OccasionServiceProvider;
use App\Domains\People\PeopleServiceProvider;
use App\Domains\Planning\PlanningServiceProvider;
use App\Domains\Shared\SharedServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    CommunicationServiceProvider::class,
    FinanceServiceProvider::class,
    IdentityServiceProvider::class,
    MediaServiceProvider::class,
    OccasionServiceProvider::class,
    PeopleServiceProvider::class,
    PlanningServiceProvider::class,
    SharedServiceProvider::class,
    AppServiceProvider::class,
    AdminPanelProvider::class,
];
