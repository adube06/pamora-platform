<?php

use App\Domains\Finance\FinanceServiceProvider;
use App\Domains\Identity\IdentityServiceProvider;
use App\Domains\Occasion\OccasionServiceProvider;
use App\Domains\People\PeopleServiceProvider;
use App\Domains\Planning\PlanningServiceProvider;
use App\Domains\Shared\SharedServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    SharedServiceProvider::class,
    IdentityServiceProvider::class,
    OccasionServiceProvider::class,
    PeopleServiceProvider::class,
    PlanningServiceProvider::class,
    FinanceServiceProvider::class,
];
