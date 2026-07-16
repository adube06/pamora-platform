<?php

namespace App\Domains\Media\Application\Services;

use App\Domains\Media\Domain\Enums\MediaType;
use App\Domains\Media\Domain\Events\MediaUploaded;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class UploadMediaService
{
    /**
     * @param  array{visibility?: string|null}  $data
     */
    public function handle(Occasion $occasion, UploadedFile $file, array $data, User $actor): MediaAsset
    {
        $path = $file->store("media/{$occasion->id}", 'local');

        $mediaAsset = MediaAsset::create([
            'occasion_id' => $occasion->id,
            'attachable_type' => Occasion::class,
            'attachable_id' => $occasion->id,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => MediaType::fromMimeType($file->getMimeType()),
            'disk' => 'local',
            'path' => $path,
            'size' => $file->getSize(),
            'visibility' => $data['visibility'] ?? 'occasion_members',
            'uploaded_by' => $actor->id,
        ]);

        MediaUploaded::dispatch($mediaAsset, $actor);

        return $mediaAsset;
    }
}
