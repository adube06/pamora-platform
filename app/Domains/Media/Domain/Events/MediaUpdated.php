<?php

namespace App\Domains\Media\Domain\Events;

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MediaUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly MediaAsset $mediaAsset,
        public readonly User $actor,
    ) {}
}
