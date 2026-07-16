<?php

namespace App\Domains\Media\Application\Services;

use App\Domains\Media\Domain\Events\AlbumCreated;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;

class CreateAlbumService
{
    /**
     * @param  array{name: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Album
    {
        $album = Album::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'created_by' => $actor->id,
        ]);

        AlbumCreated::dispatch($album, $actor);

        return $album;
    }
}
