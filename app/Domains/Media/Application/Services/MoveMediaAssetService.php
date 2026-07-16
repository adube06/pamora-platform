<?php

namespace App\Domains\Media\Application\Services;

use App\Domains\Media\Domain\Events\MediaUpdated;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;

class MoveMediaAssetService
{
    public function handle(MediaAsset $mediaAsset, ?Album $album, User $actor): MediaAsset
    {
        $mediaAsset->update($album !== null
            ? ['attachable_type' => Album::class, 'attachable_id' => $album->id]
            : ['attachable_type' => Occasion::class, 'attachable_id' => $mediaAsset->occasion_id]);

        MediaUpdated::dispatch($mediaAsset, $actor);

        return $mediaAsset;
    }
}
