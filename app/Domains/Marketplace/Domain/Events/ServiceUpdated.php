<?php

namespace App\Domains\Marketplace\Domain\Events;

use App\Domains\Marketplace\Domain\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Service $service,
        public readonly User $actor,
    ) {}
}
