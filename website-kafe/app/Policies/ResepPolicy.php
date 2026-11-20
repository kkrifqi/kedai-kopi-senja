<?php

namespace App\Policies;

use App\Models\Resep;
use App\Models\User;

class ResepPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function view(User $user, Resep $resep): bool
    {
        return $user->role === 'Manajer';
    }

    public function create(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function update(User $user, Resep $resep): bool
    {
        return $user->role === 'Manajer';
    }

    public function delete(User $user, Resep $resep): bool
    {
        return $user->role === 'Manajer';
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'Manajer';
    }
}
