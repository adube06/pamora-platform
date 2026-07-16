<?php

namespace Database\Factories;

use App\Domains\Media\Domain\Enums\MediaType;
use App\Domains\Media\Domain\Models\MediaAsset;
use App\Domains\Occasion\Domain\Models\Occasion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaAsset>
 */
class MediaAssetFactory extends Factory
{
    protected $model = MediaAsset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'attachable_type' => Occasion::class,
            // Defaults to matching occasion_id's resolved id via the
            // configure() hook below — tests overriding occasion_id
            // should also override attachable_id to keep both in sync.
            'attachable_id' => null,
            'file_name' => $this->faker->word().'.jpg',
            'file_type' => MediaType::Image,
            'disk' => 'local',
            'path' => 'media/'.$this->faker->uuid().'.jpg',
            'size' => $this->faker->numberBetween(1000, 500000),
            'visibility' => 'occasion_members',
            'uploaded_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (MediaAsset $mediaAsset) {
            $mediaAsset->attachable_id ??= $mediaAsset->occasion_id;
        });
    }
}
