<?php

namespace App\Filament\Resources\ProjetoResource\Widgets;

use App\Filament\Resources\TarefaResource;
use App\Models\EtapaPrestacao;
use App\Models\Tarefa;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class CalendarioProjetoWidget extends FullCalendarWidget
{
    public Model | int | string | null $record = null;

    protected function headerActions(): array
    {
        return [];
    }

    protected function modalActions(): array
    {
        return [];
    }

    public function fetchEvents(array $fetchInfo): array
    {
        if (! $this->record) {
            return [];
        }

        $start = Carbon::parse($fetchInfo['start'])->startOfDay();
        $end = Carbon::parse($fetchInfo['end'])->endOfDay();

        $events = [];
        $user = auth()->user();

        // Tarefas (oculto para Coordenador Financeiro)
        if (!$user?->isCoordenadorFinanceiro()) {
        $tarefas = Tarefa::query()
            ->whereHas('meta', function ($query) {
                $query->where('projeto_id', $this->record->getKey());
            })
            ->where(function ($query) use ($start, $end) {
                $query
                    ->whereBetween('data_inicio', [$start, $end])
                    ->orWhereBetween('data_fim', [$start, $end])
                    ->orWhereHas('ocorrencias', function ($q) use ($start, $end) {
                        $q->whereBetween('data_fim', [$start, $end]);
                    })
                    ->orWhere(function ($query) use ($start, $end) {
                        $query
                            ->whereNotNull('data_inicio')
                            ->whereNotNull('data_fim')
                            ->where('data_inicio', '<=', $start)
                            ->where('data_fim', '>=', $end);
                    });
            })
            ->with('polo')
            ->with('ocorrencias')
            ->get();

        foreach ($tarefas as $tarefa) {
            $color = match ($tarefa->getStatusNormalizado()) {
                'realizado' => '#22c55e',
                'em_execucao' => '#3b82f6',
                'pendente' => '#ef4444',
                default => '#6b7280',
            };

            if ($tarefa->ocorrencias->isNotEmpty()) {
                foreach ($tarefa->ocorrencias as $ocorrencia) {
                    $startDate = $ocorrencia->data_fim;
                    if (! $startDate) {
                        continue;
                    }
                    $events[] = [
                        'id' => 'tarefa-' . $tarefa->id . '-' . $ocorrencia->id,
                        'title' => '[' . $tarefa->numero . '] ' . Str::limit($tarefa->descricao, 40),
                        'start' => $startDate->toDateString(),
                        'color' => $color,
                        'url' => TarefaResource::getUrl('edit', ['record' => $tarefa]),
                        'extendedProps' => [
                            'tipo' => 'tarefa',
                            'status' => $tarefa->status_label,
                            'polo' => $tarefa->polo?->nome ?? 'NSA',
                            'responsavel' => $tarefa->responsavel,
                        ],
                    ];
                }
                continue;
            }

            $startDate = $tarefa->data_inicio ?? $tarefa->data_fim;
            if (! $startDate) {
                continue;
            }

            $events[] = [
                'id' => 'tarefa-' . $tarefa->id,
                'title' => '[' . $tarefa->numero . '] ' . Str::limit($tarefa->descricao, 40),
                'start' => $startDate->toDateString(),
                'end' => $tarefa->data_fim?->copy()->addDay()->toDateString(),
                'color' => $color,
                'url' => TarefaResource::getUrl('edit', ['record' => $tarefa]),
                'extendedProps' => [
                    'tipo' => 'tarefa',
                    'status' => $tarefa->status_label,
                    'polo' => $tarefa->polo?->nome ?? 'NSA',
                    'responsavel' => $tarefa->responsavel,
                ],
            ];
        }
        }

        // Etapas de Prestação de Contas (oculto para Diretor de Operações e Coordenador de Polo)
        if ($user && !$user->isDiretorOperacoes() && !$user->isCoordenadorPolo()) {
        $etapas = EtapaPrestacao::query()
            ->where('projeto_id', $this->record->getKey())
            ->whereBetween('data_limite', [$start, $end])
            ->with('projetoFinanciador.financiador')
            ->get();

        foreach ($etapas as $etapa) {
            $color = match ($etapa->getStatusNormalizado()) {
                'realizado' => '#22c55e',
                'em_execucao' => '#3b82f6',
                'pendente' => '#ef4444',
                default => '#6b7280',
            };

            $financiador = $etapa->projetoFinanciador?->financiador?->nome ?? 'Interna';
            $tipo = $etapa->tipo_label;

            $events[] = [
                'id' => 'etapa-' . $etapa->id,
                'title' => "[{$tipo}] {$financiador} - Etapa {$etapa->numero_etapa}",
                'start' => $etapa->data_limite->toDateString(),
                'color' => $color,
                'extendedProps' => [
                    'tipo' => 'prestacao',
                    'status' => $etapa->status_label,
                    'financiador' => $financiador,
                    'tipo_prestacao' => $etapa->tipo,
                ],
            ];
        }
        }

        return $events;
    }

    public function config(): array
    {
        return [
            'initialView' => 'dayGridMonth',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'listYear,dayGridMonth,timeGridWeek,listMonth',
            ],
            'buttonText' => [
                'today' => 'Hoje',
                'month' => 'Mês',
                'week' => 'Semana',
                'listMonth' => 'Lista',
                'listYear' => 'Ano',
            ],
            'navLinks' => true,
            'editable' => false,
            'dayMaxEvents' => 4,
            'eventDisplay' => 'block',
        ];
    }

    public function onEventClick(array $event): void
    {
        if (($event['extendedProps']['tipo'] ?? null) === 'prestacao') {
            Notification::make()
                ->title($event['title'])
                ->body('Status: ' . ($event['extendedProps']['status'] ?? '-'))
                ->info()
                ->send();
        }
    }
}
