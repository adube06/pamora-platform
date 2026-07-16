<?php

namespace App\Domains\Media\Application\Services;

use App\Domains\Media\Domain\Events\AlbumCreated;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;

class CreateAlbumService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * @param  array{name: string}  $data
     */
    public function handle(Occasion $occasion, array $data, User $actor): Album
    {
        $this->ensureOccasionAcceptsActivity($occasion);

        $album = Album::create([
            ...$data,
            'occasion_id' => $occasion->id,
            'created_by' => $actor->id,
        ]);

        AlbumCreated::dispatch($album, $actor);

        return $album;
    }
}
