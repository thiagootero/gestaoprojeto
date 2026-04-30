<?php

namespace App\Support;

use App\Models\HistoricoTarefa;
use App\Models\Tarefa;
use App\Models\TarefaOcorrencia;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class TarefaHistoricoService
{
    public static function registrarMovimentacao(
        Tarefa $tarefa,
        User $user,
        ?string $statusAnterior,
        string $statusNovo,
        ?string $observacao = null,
        ?TarefaOcorrencia $ocorrencia = null,
    ): void {
        HistoricoTarefa::create([
            'tarefa_id' => $tarefa->id,
            'tarefa_ocorrencia_id' => $ocorrencia?->id,
            'user_id' => $user->id,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'observacao' => $observacao,
            'created_at' => now(),
        ]);
    }

    public static function renderizarHistorico(Tarefa $tarefa, ?int $ocorrenciaId = null): HtmlString
    {
        $tarefa->loadMissing([
            'realizacoes.user',
            'validadoPorUser',
            'ocorrencias.validadoPorUser',
            'historicoTarefas.user',
        ]);

        $historicos = self::filtrarHistorico($tarefa->historicoTarefas, $ocorrenciaId);

        if ($historicos->isEmpty()) {
            return self::renderizarHistoricoLegado($tarefa, $ocorrenciaId);
        }

        $html = '<div class="space-y-4">';

        foreach ($historicos as $historico) {
            [$label, $cardClass, $badgeClass] = self::mapearVisual($historico->status_novo);
            $nome = e($historico->user?->name ?? 'Usuário removido');
            $data = $historico->created_at?->format('d/m/Y H:i') ?? '';
            $observacao = $historico->observacao !== null ? e($historico->observacao) : null;

            $html .= '<div class="' . $cardClass . ' rounded-r-lg p-3">';
            $html .= '<div class="flex items-center gap-2 mb-1">';
            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $badgeClass . '">' . $label . '</span>';
            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-white">' . $nome . '</span>';
            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $data . '</span>';
            $html .= '</div>';

            if ($observacao !== null && $observacao !== '') {
                $html .= '<p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">' . $observacao . '</p>';
            }

            $html .= '</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function filtrarHistorico(Collection $historicos, ?int $ocorrenciaId): Collection
    {
        return $historicos
            ->when(
                $ocorrenciaId !== null,
                fn (Collection $items) => $items->where('tarefa_ocorrencia_id', $ocorrenciaId),
                fn (Collection $items) => $items->whereNull('tarefa_ocorrencia_id'),
            )
            ->sortBy('created_at')
            ->values();
    }

    protected static function mapearVisual(string $status): array
    {
        return match ($status) {
            'em_analise' => [
                'Enviado',
                'border-l-4 border-blue-400 bg-blue-50 dark:bg-blue-950 dark:border-blue-600',
                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            ],
            'devolvido' => [
                'Devolvido',
                'border-l-4 border-red-400 bg-red-50 dark:bg-red-950 dark:border-red-600',
                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            ],
            'com_ressalvas' => [
                'Validado com ressalva',
                'border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600',
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            ],
            'realizado', 'concluido' => [
                'Validado',
                'border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600',
                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            ],
            default => [
                ucfirst(str_replace('_', ' ', $status)),
                'border-l-4 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:border-gray-600',
                'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
            ],
        };
    }

    protected static function renderizarHistoricoLegado(Tarefa $tarefa, ?int $ocorrenciaId = null): HtmlString
    {
        $html = '<div class="space-y-4">';

        $realizacoes = $tarefa->realizacoes
            ->when($ocorrenciaId, fn ($q) => $q->where('tarefa_ocorrencia_id', $ocorrenciaId))
            ->sortBy('created_at');

        foreach ($realizacoes as $realizacao) {
            $nome = e($realizacao->user?->name ?? 'Usuário removido');
            $data = $realizacao->created_at?->format('d/m/Y H:i') ?? '';
            $comentario = e($realizacao->comentario);

            $html .= '<div class="border-l-4 border-blue-400 bg-blue-50 dark:bg-blue-950 dark:border-blue-600 rounded-r-lg p-3">';
            $html .= '<div class="flex items-center gap-2 mb-1">';
            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">Enviado</span>';
            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-white">' . $nome . '</span>';
            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $data . '</span>';
            $html .= '</div>';
            $html .= '<p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">' . $comentario . '</p>';
            $html .= '</div>';
        }

        $observacoes = $tarefa->observacoes;
        if ($ocorrenciaId) {
            $observacoes = $tarefa->ocorrencias->firstWhere('id', (int) $ocorrenciaId)?->observacoes;
        }

        if ($observacoes) {
            $linhas = array_filter(explode("\n", $observacoes));
            $dataLegada = ($ocorrenciaId
                ? $tarefa->ocorrencias->firstWhere('id', (int) $ocorrenciaId)?->updated_at
                : $tarefa->updated_at)?->format('d/m/Y H:i') ?? '';

            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if ($linha === '') {
                    continue;
                }

                if (str_starts_with($linha, '[Devolvido por ') || str_starts_with($linha, '[Rejeitado por ')) {
                    $html .= '<div class="border-l-4 border-red-400 bg-red-50 dark:bg-red-950 dark:border-red-600 rounded-r-lg p-3">';
                    $html .= '<div class="flex items-center gap-2 mb-1">';
                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Devolvido</span>';
                    if ($dataLegada !== '') {
                        $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $dataLegada . '</span>';
                    }
                    $html .= '</div>';
                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                    $html .= '</div>';
                } elseif (str_starts_with($linha, '[Aprovado com ressalvas por ')) {
                    $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                    $html .= '<div class="flex items-center gap-2 mb-1">';
                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Validado com ressalva</span>';
                    if ($dataLegada !== '') {
                        $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $dataLegada . '</span>';
                    }
                    $html .= '</div>';
                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                    $html .= '</div>';
                } elseif (str_starts_with($linha, '[Validado por ') || str_starts_with($linha, '[Validação]')) {
                    $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                    $html .= '<div class="flex items-center gap-2 mb-1">';
                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aprovado</span>';
                    if ($dataLegada !== '') {
                        $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $dataLegada . '</span>';
                    }
                    $html .= '</div>';
                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                    $html .= '</div>';
                } else {
                    $html .= '<div class="border-l-4 border-gray-300 bg-gray-50 dark:bg-gray-900 dark:border-gray-600 rounded-r-lg p-3">';
                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                    $html .= '</div>';
                }
            }
        }

        $validadoPor = $tarefa->validadoPorUser;
        $validadoEm = $tarefa->validado_em;
        $statusValidacao = $tarefa->status;

        if ($ocorrenciaId) {
            $ocorrencia = $tarefa->ocorrencias->firstWhere('id', (int) $ocorrenciaId);
            $validadoPor = $ocorrencia?->validadoPorUser;
            $validadoEm = $ocorrencia?->validado_em;
            $statusValidacao = $ocorrencia?->status;
        }

        if ($validadoPor && $validadoEm) {
            $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
            $html .= '<div class="flex items-center gap-2">';
            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">'
                . ($statusValidacao === 'com_ressalvas' ? 'Validado com ressalva' : 'Validado') . '</span>';
            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-white">' . e($validadoPor->name) . '</span>';
            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $validadoEm->format('d/m/Y H:i') . '</span>';
            $html .= '</div>';
            $html .= '</div>';
        }

        if ($realizacoes->isEmpty() && !$observacoes && !$validadoPor) {
            $html .= '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhum registro no histórico.</p>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }
}
