<?php

namespace App\Policies;

use App\Models\Projeto;
use App\Models\User;

class ProjetoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Projeto $projeto): bool
    {
        // Coordenador de polo: só projetos dos seus polos ou de polos gerais
        if ($user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');

            return $projeto->polos()
                ->where(function ($q) use ($poloIds) {
                    $q->whereIn('polos.id', $poloIds)
                        ->orWhere('polos.is_geral', true);
                })
                ->exists();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function update(User $user, Projeto $projeto): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function delete(User $user, Projeto $projeto): bool
    {
        return $user->isAdminGeral();
    }

    public function restore(User $user, Projeto $projeto): bool
    {
        return $user->isAdminGeral();
    }

    public function forceDelete(User $user, Projeto $projeto): bool
    {
        return $user->isAdminGeral();
    }
}
