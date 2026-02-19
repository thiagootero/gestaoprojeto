<?php

namespace App\Filament\Widgets;

use App\Models\Projeto;
use Filament\Widgets\Widget;

class ProgressoProjetos extends Widget
{
    protected static string $view = 'filament.widgets.progresso-projetos';

    protected static ?string $heading = 'Progresso dos Projetos';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getProjetos(): \Illuminate\Support\Collection
    {
        return Projeto::query()
            ->where('projetos.status', 'em_execucao')
            ->with(['metas.tarefas'])
            ->get()
            ->map(function ($projeto) {
                $totalTarefas = $projeto->tarefas()->count();
                $tarefasConcluidas = $projeto->tarefas()->whereIn('tarefas.status', ['realizado', 'concluido', 'com_ressalvas'])->count();
                $percentual = $totalTarefas > 0 ? round(($tarefasConcluidas / $totalTarefas) * 100, 1) : 0;

                return [
                    'id' => $projeto->id,
                    'nome' => $projeto->nome,
                    'total' => $totalTarefas,
                    'concluidas' => $tarefasConcluidas,
                    'percentual' => $percentual,
                    'cor' => $this->getCorProgresso($percentual),
                ];
            });
    }

    private function getCorProgresso(float $percentual): string
    {
        if ($percentual >= 75) return 'bg-green-500';
        if ($percentual >= 50) return 'bg-blue-500';
        if ($percentual >= 25) return 'bg-yellow-500';
        return 'bg-red-500';
    }
}
