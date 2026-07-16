<?php

namespace App\Domains\Media\Domain\Models;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Concerns\HasUuid;
use App\Models\User;
use Database\Factories\AlbumFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Album extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected static function newFactory(): AlbumFactory
    {
        return AlbumFactory::new();
    }

    protected $fillable = [
        'occasion_id',
        'name',
        'created_by',
    ];

    public function occasion(): BelongsTo
    {
        return $this->belongsTo(Occasion::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mediaAssets(): MorphMany
    {
        return $this->morphMany(MediaAsset::class, 'attachable');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
