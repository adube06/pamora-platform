<?php

namespace App\Domains\Occasion\Presentation\Policies;

use App\Domains\Occasion\Domain\Models\Occasion;
use App\Domains\Shared\Domain\Enums\Permission;
use App\Models\User;

class OccasionPolicy
{
    public function view(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user) !== null;
    }

    public function update(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user)?->hasPermission(Permission::OccasionEdit) ?? false;
    }

    public function archive(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user)?->hasPermission(Permission::OccasionArchive) ?? false;
    }

    public function cancel(User $user, Occasion $occasion): bool
    {
        return $occasion->memberFor($user)?->hasPermission(Permission::OccasionCancel) ?? false;
    }
}
