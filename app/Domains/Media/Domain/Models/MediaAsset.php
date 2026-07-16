<?php

namespace App\Domains\Media\Domain\Models;

use App\Domains\Media\Domain\Enums\MediaType;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaAsset extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): MediaAssetFactory
    {
        return MediaAssetFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'attachable_type',
        'attachable_id',
        'file_name',
        'file_type',
        'disk',
        'path',
        'size',
        'visibility',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'file_type' => MediaType::class,
        ];
    }

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
