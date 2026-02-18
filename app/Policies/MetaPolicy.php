<?php

namespace App\Policies;

use App\Models\Meta;
use App\Models\User;

class MetaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Meta $meta): bool
    {
        if ($user->isAdminGeral() || $user->isDiretorProjetos() || $user->isDiretorOperacoes()) {
            return true;
        }

        // Coordenador de polo só vê metas de projetos dos seus polos ou polos gerais
        if ($user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');

            return $meta->projeto->polos()
                ->where(function ($q) use ($poloIds) {
                    $q->whereIn('polos.id', $poloIds)
                        ->orWhere('polos.is_geral', true);
                })
                ->exists();
        }

        if ($user->isCoordenadorFinanceiro()) {
            return $meta->tarefas()
                ->where(function ($query) use ($user) {
                    $query->where('responsavel_user_id', $user->id)
                        ->orWhereHas('responsaveis', fn ($q) => $q->where('users.id', $user->id));
                })
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function update(User $user, Meta $meta): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function delete(User $user, Meta $meta): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }
}
