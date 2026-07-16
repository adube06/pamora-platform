<?php

namespace App\Domains\Media;

use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Media\Presentation\Policies\MediaAssetPolicy;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);

        // Uploading is checked against the Occasion, not an existing
        // MediaAsset — same pattern as create-task/manage-checklist.
        // Album creation reuses this same ability (Design Decision 2).
        Gate::define('upload-media', function (User $user, Occasion $occasion) {
            return $occasion->memberFor($user)?->hasPermission(Permission::MediaUpload) ?? false;
        });

        // Instance-bound (targets a specific MediaAsset), unlike
        // upload-media's Occasion-scoped shape — mirrors
        // MediaAssetPolicy::download()'s own instance-bound pattern.
        Gate::define('edit-media-metadata', function (User $user, MediaAsset $mediaAsset) {
            return $mediaAsset->occasion->memberFor($user)?->hasPermission(Permission::MediaEditMetadata) ?? false;
        });

        Route::middleware('web')
            ->group(__DIR__.'/routes-web.php');

        Route::prefix('api/v1')
            ->middleware('api')
            ->group(__DIR__.'/routes-api.php');
    }
}
