<?php

namespace App\Domains\Media\Presentation\Policies;

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function download(User $user, MediaAsset $mediaAsset): bool
    {
        if ($mediaAsset->uploaded_by === $user->id) {
            return true;
        }

        return $mediaAsset->visibility === 'occasion_members'
            && $mediaAsset->occasion->memberFor($user) !== null;
    }
}
