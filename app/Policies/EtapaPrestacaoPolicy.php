<?php

namespace App\Policies;

use App\Models\EtapaPrestacao;
use App\Models\User;

class EtapaPrestacaoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EtapaPrestacao $etapa): bool
    {
        if ($user->isAdminGeral() || $user->isDiretorProjetos() || $user->isDiretorOperacoes()) {
            return true;
        }

        if ($user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');

            return $etapa->projeto?->polos()
                ->where(function ($q) use ($poloIds) {
                    $q->whereIn('polos.id', $poloIds)
                        ->orWhere('polos.is_geral', true);
                })
                ->exists();
        }

        if ($user->isCoordenadorFinanceiro()) {
            return $etapa->projeto?->tarefas()
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

    public function update(User $user, EtapaPrestacao $etapa): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, EtapaPrestacao $etapa): bool
    {
        return $this->create($user);
    }

    public function validar(User $user): bool
    {
        return $user->isAdminGeral() || $user->isDiretorOperacoes() || $user->isDiretorProjetos();
    }
}
