<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isDiretorOperacoes();
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral();
    }

    public function update(User $user, User $model): bool
    {
        if ($user->isAdminGeral()) {
            return true;
        }

        // Usuário pode editar apenas seu próprio perfil (exceto campo perfil)
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdminGeral() && $user->id !== $model->id;
    }
}
