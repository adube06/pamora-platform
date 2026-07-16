<?php

namespace App\Domains\Media\Presentation\Http\Resources;

use App\Domains\Finance\Domain\Models\Expense;
use App\Domains\Media\Domain\Models\Album;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Planning\Domain\Models\Task;
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
            // Numeric id (not uuid) deliberately, matching the
            // move-media-asset endpoint's album_id/task_id — these
            // fields exist so the Media page's dropdown can match the
            // asset's current target against the album/task option
            // list, both of which are raw models keyed by numeric id.
            'album' => $this->attachable instanceof Album
                ? ['id' => $this->attachable->id, 'name' => $this->attachable->name]
                : null,
            'task' => $this->attachable instanceof Task
                ? ['id' => $this->attachable->id, 'title' => $this->attachable->title]
                : null,
            'expense' => $this->attachable instanceof Expense
                ? ['id' => $this->attachable->id, 'description' => $this->attachable->description ?? ('Expense of '.$this->attachable->amount.' '.$this->attachable->currency)]
                : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
