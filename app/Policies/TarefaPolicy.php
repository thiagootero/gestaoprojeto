<?php

namespace App\Policies;

use App\Models\Tarefa;
use App\Models\User;

class TarefaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tarefa $tarefa): bool
    {
        if ($user->isAdminGeral() || $user->isDiretorProjetos() || $user->isDiretorOperacoes()) {
            return true;
        }

        // Coordenador de polo só vê tarefas dos seus polos, polos gerais, ou sem polo
        if ($user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');
            $geralIds = \App\Models\Polo::where('is_geral', true)->pluck('id');
            $allIds = $poloIds->merge($geralIds);

            return $allIds->contains($tarefa->polo_id) || $tarefa->polo_id === null;
        }

        if ($user->isCoordenadorFinanceiro()) {
            return $tarefa->responsavel_user_id === $user->id
                || $tarefa->responsaveis()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    public function update(User $user, Tarefa $tarefa): bool
    {
        if ($user->isAdminGeral() || $user->isDiretorProjetos()) {
            return true;
        }

        // Diretor de Operações pode editar status e observações
        if ($user->isDiretorOperacoes()) {
            return true;
        }

        // Coordenador de polo pode editar tarefas dos seus polos ou polos gerais
        if ($user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');
            $geralIds = \App\Models\Polo::where('is_geral', true)->pluck('id');

            return $poloIds->merge($geralIds)->contains($tarefa->polo_id);
        }

        if ($user->isCoordenadorFinanceiro()) {
            return $tarefa->responsavel_user_id === $user->id
                || $tarefa->responsaveis()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public function delete(User $user, Tarefa $tarefa): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }

    /**
     * Validação de comprovação só para Diretor de Projetos e Super Admin
     */
    public function validarComprovacao(User $user, Tarefa $tarefa): bool
    {
        return $user->isAdminGeral() || $user->isDiretorProjetos();
    }
}
