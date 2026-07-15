<?php

namespace Database\Factories;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Models\OccasionMember;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OccasionMember>
 */
class OccasionMemberFactory extends Factory
{
    protected $model = OccasionMember::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occasion_id' => Occasion::factory(),
            'user_id' => User::factory(),
            'invitation_id' => null,
            'status' => OccasionMemberStatus::Active,
            'responsibilities' => [],
            'permissions' => [],
        ];
    }

    public function host(): self
    {
        return $this->state(fn () => [
            'permissions' => Permission::hostDefaults(),
        ]);
    }
}
