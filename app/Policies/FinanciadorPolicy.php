<?php

namespace App\Policies;

use App\Models\Financiador;
use App\Models\User;

class FinanciadorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function view(User $user, Financiador $financiador): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function update(User $user, Financiador $financiador): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function delete(User $user, Financiador $financiador): bool
    {
        return $user->isAdminGeral();
    }
}
