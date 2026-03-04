<?php

namespace App\Filament\Pages;

use App\Models\Projeto;
use App\Models\TarefaRealizacao;
use Illuminate\Support\Collection;

class DevolutivaTarefas extends MinhasTarefas
{
    protected static ?string $title = 'Devolutiva de Tarefas';

    protected static ?string $navigationLabel = 'Devolutiva de Tarefas';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'devolutiva-tarefas';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAdminGeral()
            || $user->isDiretorProjetos()
            || $user->isCoordenadorPolo();
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
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $items = collect($mensal)->flatMap(fn (array $mes) => $mes['items'] ?? collect());
        if ($items->isEmpty()) {
            return $mensal;
        }

        $lastSenders = $this->getLastSendersByItem($items);

        $poloIdsDoCoordenador = $user->isCoordenadorPolo()
            ? $user->polos->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        $mensal = array_map(function (array $mes) use ($user, $lastSenders, $poloIdsDoCoordenador) {
            $itemsFiltrados = collect($mes['items'] ?? [])->filter(function ($item) use ($user, $lastSenders, $poloIdsDoCoordenador) {
                $key = $this->buildItemKey((int) $item['id'], $item['ocorrencia_id'] ?? null);
                $sender = $lastSenders->get($key);
                $status = $item['status_normalizado'] ?? '';
                $poloId = isset($item['polo_id']) ? (int) $item['polo_id'] : null;

                if ($user->isCoordenadorPolo()) {
                    return $status === 'devolvido'
                        && $sender?->id === $user->id
                        && $poloId !== null
                        && in_array($poloId, $poloIdsDoCoordenador, true);
                }

                if ($user->isDiretorProjetos() || $user->isAdminGeral() || $user->isSuperAdmin()) {
                    return $status === 'em_analise' && $sender?->perfil === 'coordenador_polo';
                }

                return false;
            })->values();

            return [
                'label' => $mes['label'],
                'items' => $itemsFiltrados,
            ];
        }, $mensal);

        return array_values(array_filter($mensal, fn (array $mes) => ($mes['items'] ?? collect())->isNotEmpty()));
    }

    public function getProjetosOptions(): array
    {
        $query = Projeto::query()->orderBy('nome');

        $user = auth()->user();
        if ($user && $user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');
            $query->whereHas('polos', function ($q) use ($poloIds) {
                $q->whereIn('polos.id', $poloIds)
                    ->orWhere('polos.is_geral', true);
            });
        }

        return $query->pluck('nome', 'id')->toArray();
    }

    public function getItemLabelSingular(): string
    {
        return 'pendência';
    }

    public function getItemLabelPlural(): string
    {
        return 'pendências';
    }

    private function getLastSendersByItem(Collection $items): Collection
    {
        $tarefaIds = $items->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();
        $realizacoes = TarefaRealizacao::query()
            ->whereIn('tarefa_id', $tarefaIds)
            ->with('user:id,perfil')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $map = collect();
        foreach ($realizacoes as $realizacao) {
            $key = $this->buildItemKey((int) $realizacao->tarefa_id, $realizacao->tarefa_ocorrencia_id);
            $map->put($key, $realizacao->user);
        }

        return $map;
    }

    private function buildItemKey(int $tarefaId, $ocorrenciaId): string
    {
        $suffix = $ocorrenciaId ? (string) $ocorrenciaId : 'base';

        return $tarefaId . ':' . $suffix;
    }
}
