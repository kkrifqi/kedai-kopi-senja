<?php

namespace App\Policies;

use App\Models\StokMasuk;
use App\Models\User;

class StokMasukPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function view(User $user, StokMasuk $stokMasuk): bool
    {
        return $user->role === 'Manajer';
    }

    public function create(User $user): bool
    {
        return $user->role === 'Manajer';
    }

    public function update(User $user, StokMasuk $stokMasuk): bool
    {
        return false;
    }

    public function delete(User $user, StokMasuk $stokMasuk): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
