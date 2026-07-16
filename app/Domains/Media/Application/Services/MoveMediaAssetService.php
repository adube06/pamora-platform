<?php

namespace App\Domains\Media\Application\Services;

use App\Domains\Media\Domain\Events\MediaUpdated;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Application\Concerns\GuardsAgainstArchivedOccasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MoveMediaAssetService
{
    use GuardsAgainstArchivedOccasion;

    /**
     * $attachable may be an Album, a Task, or any future attachable
     * entity — null means "back to the Occasion's general gallery."
     */
    public function handle(MediaAsset $mediaAsset, ?Model $attachable, User $actor): MediaAsset
    {
        $this->ensureOccasionAcceptsActivity($mediaAsset->occasion);

        $mediaAsset->update($attachable !== null
            ? ['attachable_type' => $attachable::class, 'attachable_id' => $attachable->id]
            : ['attachable_type' => Occasion::class, 'attachable_id' => $mediaAsset->occasion_id]);

        MediaUpdated::dispatch($mediaAsset, $actor);

        return $mediaAsset;
    }
}
