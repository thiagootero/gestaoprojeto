<?php

namespace App\Support;

use App\Models\EtapaPrestacao;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificacaoCentral
{
    public static function adminsEDiretoresProjetos(): Collection
    {
        return User::query()
            ->where('ativo', true)
            ->whereIn('perfil', ['super_admin', 'admin_geral', 'diretor_projetos'])
            ->get();
    }

    public static function adminsEDiretoresPrestacao(): Collection
    {
        return User::query()
            ->where('ativo', true)
            ->whereIn('perfil', ['super_admin', 'admin_geral', 'diretor_projetos', 'diretor_operacoes'])
            ->get();
    }

    public static function ultimoEnvioTarefa(Tarefa $tarefa): ?User
    {
        $realizacao = $tarefa->realizacoes()->latest()->with('user')->first();

        return $realizacao?->user;
    }

    public static function ultimoEnvioPrestacao(EtapaPrestacao $etapa): ?User
    {
        $realizacao = $etapa->realizacoes()->latest()->with('user')->first();

        return $realizacao?->user;
    }
}
