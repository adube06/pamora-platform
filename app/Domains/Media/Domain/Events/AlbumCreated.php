<?php

namespace App\Domains\Media\Domain\Events;

use App\Domains\Media\Domain\Models\Album;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlbumCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Album $album,
        public readonly User $actor,
    ) {}
}
