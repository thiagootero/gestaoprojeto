<?php

namespace App\Support;

use App\Models\Tarefa;
use App\Models\User;
use App\Notifications\TarefaPrazoProximo;
use Illuminate\Support\Collection;

class TarefaPrazoReminderService
{
    /**
     * @return array{sent:int, tasks:int}
     */
    public function enviarLembretes(array $diasAlvo = [7, 1]): array
    {
        $diasAlvo = collect($diasAlvo)
            ->map(fn ($dias) => (int) $dias)
            ->filter(fn ($dias) => $dias > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($diasAlvo)) {
            return ['sent' => 0, 'tasks' => 0];
        }

        $hoje = now()->startOfDay();
        $datasAlvo = collect($diasAlvo)
            ->map(fn ($dias) => $hoje->copy()->addDays($dias)->toDateString())
            ->values()
            ->all();

        $tarefas = Tarefa::query()
            ->with([
                'meta.projeto',
                'ocorrencias',
                'responsavelUser:id,name,email,ativo',
                'responsaveis:id,name,email,ativo',
            ])
            ->where(function ($query) use ($datasAlvo) {
                $query->whereIn('data_fim', $datasAlvo)
                    ->orWhereHas('ocorrencias', fn ($q) => $q->whereIn('data_fim', $datasAlvo));
            })
            ->get();

        $enviadas = 0;

        foreach ($tarefas as $tarefa) {
            $destinatarios = $this->getDestinatarios($tarefa);
            if ($destinatarios->isEmpty()) {
                continue;
            }

            if ($tarefa->ocorrencias->isNotEmpty()) {
                foreach ($tarefa->ocorrencias as $ocorrencia) {
                    $diasRestantes = $hoje->diffInDays($ocorrencia->data_fim, false);
                    if (!in_array($diasRestantes, $diasAlvo, true)) {
                        continue;
                    }
                    if (in_array($ocorrencia->getStatusNormalizado(), ['realizado', 'com_ressalvas'], true)) {
                        continue;
                    }

                    foreach ($destinatarios as $destinatario) {
                        $destinatario->notify(new TarefaPrazoProximo($tarefa, $diasRestantes, $ocorrencia));
                        $enviadas++;
                    }
                }

                continue;
            }

            if (!$tarefa->data_fim) {
                continue;
            }

            $diasRestantes = $hoje->diffInDays($tarefa->data_fim, false);
            if (!in_array($diasRestantes, $diasAlvo, true)) {
                continue;
            }
            if (in_array($tarefa->getStatusNormalizado(), ['realizado', 'com_ressalvas'], true)) {
                continue;
            }

            foreach ($destinatarios as $destinatario) {
                $destinatario->notify(new TarefaPrazoProximo($tarefa, $diasRestantes));
                $enviadas++;
            }
        }

        return [
            'sent' => $enviadas,
            'tasks' => $tarefas->count(),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function getDestinatarios(Tarefa $tarefa): Collection
    {
        $destinatarios = collect();

        if ($tarefa->responsavelUser && $tarefa->responsavelUser->ativo) {
            $destinatarios->push($tarefa->responsavelUser);
        }

        foreach ($tarefa->responsaveis as $responsavel) {
            if ($responsavel->ativo) {
                $destinatarios->push($responsavel);
            }
        }

        return $destinatarios
            ->unique('id')
            ->values();
    }
}
