<?php

namespace App\Filament\Pages;

use Carbon\Carbon;

class TarefasPendentesCoordenadores extends MinhasTarefas
{
    protected static ?string $title = 'Tarefas Pendentes de Coordenadores';

    protected static ?string $navigationLabel = 'Tarefas Pendentes de Coordenadores';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'tarefas-pendentes-coordenadores';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAdminGeral()
            || $user->isDiretorProjetos();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected function getModo(): string
    {
        return 'operacional';
    }

    public function getTarefasMensal(): array
    {
        $mensal = parent::getTarefasMensal();
        $hoje = Carbon::today();

        $mensal = array_map(function (array $mes) use ($hoje) {
            $items = collect($mes['items'] ?? [])->filter(function (array $item) use ($hoje) {
                if (($item['tipo_item'] ?? null) !== 'tarefa') {
                    return false;
                }

                if (empty($item['polo_id'])) {
                    return false;
                }

                $status = $item['status_normalizado'] ?? 'pendente';
                if (!in_array($status, ['pendente', 'em_execucao', 'devolvido'], true)) {
                    return false;
                }

                if (empty($item['prazo'])) {
                    return false;
                }

                return Carbon::parse($item['prazo'])->startOfDay()->lt($hoje);
            })->values();

            return [
                'label' => $mes['label'],
                'items' => $items,
            ];
        }, $mensal);

        return array_values(array_filter($mensal, fn (array $mes) => ($mes['items'] ?? collect())->isNotEmpty()));
    }

    public function getItemLabelSingular(): string
    {
        return 'tarefa pendente';
    }

    public function getItemLabelPlural(): string
    {
        return 'tarefas pendentes';
    }

    public function canSendTaskItem(array $item): bool
    {
        return false;
    }

    public function canAnalyzeTaskItem(array $item): bool
    {
        return false;
    }

    public function shouldShowOpenTaskButton(array $item): bool
    {
        return true;
    }
}
