<?php

namespace App\Policies;

use App\Models\Bahan;
use App\Models\User;

class BahanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function view(User $user, Bahan $bahan): bool
    {
        return $user->role === 'Manajer';
    }

    public function create(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function update(User $user, Bahan $bahan): bool
    {
        return $user->role === 'Manajer';
    }

    public function delete(User $user, Bahan $bahan): bool
    {
        return $user->role === 'Manajer';
    }

    public function deleteAny(User $user): bool
    {
        return $user->role === 'Manajer';
    }
}
