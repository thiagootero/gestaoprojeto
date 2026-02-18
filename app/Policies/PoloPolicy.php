<?php

namespace App\Policies;

use App\Models\Polo;
use App\Models\User;

class PoloPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function view(User $user, Polo $polo): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, Polo $polo): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function delete(User $user, Polo $polo): bool
    {
        return $user->isAdminGeral();
    }
}
