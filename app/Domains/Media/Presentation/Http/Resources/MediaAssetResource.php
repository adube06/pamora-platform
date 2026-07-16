<?php

namespace App\Domains\Media\Presentation\Http\Resources;

use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MediaAsset
 */
class MediaAssetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type->value,
            'size' => $this->size,
            'visibility' => $this->visibility,
            'download_url' => route('media.download', $this->uuid),
            'uploaded_by' => $this->uploadedBy->name,
            'album' => $this->attachable instanceof Album
                ? ['id' => $this->attachable->uuid, 'name' => $this->attachable->name]
                : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
