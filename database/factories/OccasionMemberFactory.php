<?php

namespace Database\Factories;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\People\Domain\Enums\OccasionMemberStatus;
use App\Domains\People\Domain\Enums\Role;
use App\Domains\People\Domain\Models\OccasionMember;
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
            'role' => Role::Member,
            'notes' => null,
            'permissions' => Role::Member->permissions(),
        ];
    }

    public function host(): self
    {
        return $this->state(fn () => [
            'role' => Role::Host,
            'permissions' => Role::Host->permissions(),
        ]);
    }

    public function role(Role $role): self
    {
        return $this->state(fn () => [
            'role' => $role,
            'permissions' => $role->permissions(),
        ]);
    }
}
