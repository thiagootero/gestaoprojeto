<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;

class ViewProjeto extends ViewRecord
{
    protected static string $resource = ProjetoResource::class;

    protected static ?string $navigationLabel = 'Ver Projeto';

    protected static string $view = 'filament.resources.projeto-resource.pages.view-projeto';

    public function getBreadcrumbs(): array
    {
        return [
            ProjetoResource::getUrl('index') => 'Projetos',
            $this->record->nome,
        ];
    }

    public function mount(int | string $record): void
    {
        parent::mount($record);
        $this->record->load([
            'metas.tarefas.polo',
            'metas.tarefas.ocorrencias',
            'metas.tarefas.responsaveis',
            'etapasPrestacao.projetoFinanciador.financiador',
            'anexos',
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => auth()->user()?->isAdminGeral() || auth()->user()?->isDiretorProjetos()),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    public function getMetas(): Collection
    {
        return $this->record->metas->map(function ($meta) {
            $tarefas = $meta->tarefas->flatMap(function ($tarefa) {
                $responsaveis = $tarefa->responsaveis->pluck('name')->filter()->values();
                $responsavelTexto = $responsaveis->isNotEmpty()
                    ? $responsaveis->join(', ')
                    : ($tarefa->responsavel ?? '-');

                if ($tarefa->ocorrencias->isNotEmpty()) {
                    return $tarefa->ocorrencias->map(function ($ocorrencia) use ($tarefa, $responsavelTexto) {
                        $dataFim = $ocorrencia->data_fim;
                        $diasRestantes = now()->startOfDay()->diffInDays($dataFim, false);
                        $atrasada = $dataFim->isPast() && !in_array($tarefa->status, ['realizado', 'concluido', 'com_ressalvas']);
                        $vencendo = $diasRestantes >= 0 && $diasRestantes <= 7 && !in_array($tarefa->status, ['realizado', 'concluido', 'com_ressalvas']);

                        return [
                            'id' => $tarefa->id . '-' . $ocorrencia->id,
                            'numero' => $tarefa->numero,
                            'descricao' => $tarefa->descricao,
                            'polo' => $tarefa->polo?->nome ?? 'NSA',
                            'responsavel' => $responsavelTexto,
                            'data_inicio' => $tarefa->data_inicio?->format('d/m/Y'),
                            'data_fim' => $dataFim?->format('d/m/Y'),
                            'status' => $tarefa->status,
                            'status_label' => $tarefa->status_label,
                            'status_color' => $tarefa->status_color,
                            'atrasada' => $atrasada,
                            'vencendo' => $vencendo,
                        ];
                    });
                }

                return [[
                    'id' => $tarefa->id,
                    'numero' => $tarefa->numero,
                    'descricao' => $tarefa->descricao,
                    'polo' => $tarefa->polo?->nome ?? 'NSA',
                    'responsavel' => $responsavelTexto,
                    'data_inicio' => $tarefa->data_inicio?->format('d/m/Y'),
                    'data_fim' => $tarefa->data_fim?->format('d/m/Y'),
                    'status' => $tarefa->status,
                    'status_label' => $tarefa->status_label,
                    'status_color' => $tarefa->status_color,
                    'atrasada' => $tarefa->atrasada,
                    'vencendo' => $tarefa->vencendo,
                ]];
            });

            return [
                'id' => $meta->id,
                'numero' => $meta->numero,
                'descricao' => $meta->descricao,
                'status' => $meta->status,
                'status_label' => $meta->status_label,
                'status_color' => $meta->status_color,
                'percentual' => $meta->percentual_conclusao,
                'tarefas' => $tarefas,
                'total_tarefas' => $tarefas->count(),
                'tarefas_concluidas' => $tarefas->whereIn('status', ['realizado', 'concluido', 'com_ressalvas'])->count(),
            ];
        });
    }

    public function getPrestacoes(): array
    {
        $todas = $this->record->etapasPrestacao->map(function ($etapa) {
            $financiador = $etapa->projetoFinanciador?->financiador;

            return [
                'id' => $etapa->id,
                'origem' => $etapa->origem,
                'financiador' => $financiador?->nome ?? 'Interna',
                'descricao' => $etapa->descricao,
                'numero_etapa' => $etapa->numero_etapa,
                'tipo' => $etapa->tipo,
                'tipo_label' => $etapa->tipo_label,
                'data_limite' => $etapa->data_limite?->format('d/m/Y'),
                'data_limite_raw' => $etapa->data_limite,
                'status' => $etapa->status,
                'status_label' => $etapa->status_label,
                'status_color' => $etapa->status_color,
                'dias_restantes' => $etapa->dias_restantes,
                'urgencia_color' => $etapa->urgencia_color,
            ];
        })->sortBy('data_limite_raw');

        return [
            'internas' => $todas->where('origem', 'interna')->values(),
            'por_financiador' => $todas
                ->where('origem', 'financiador')
                ->groupBy('financiador')
                ->map(fn ($items) => $items->values()),
        ];
    }
}
