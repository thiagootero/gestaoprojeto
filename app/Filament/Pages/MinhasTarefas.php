<?php

namespace App\Filament\Pages;

use App\Models\Polo;
use App\Models\Projeto;
use App\Models\Tarefa;
use App\Models\TarefaRealizacao;
use App\Notifications\TarefaAprovada;
use App\Notifications\TarefaDevolvida;
use App\Notifications\TarefaEnviadaParaAnalise;
use App\Support\NotificacaoCentral;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Illuminate\Support\HtmlString;

class MinhasTarefas extends Page implements HasActions
{
    use InteractsWithActions;

    protected static string $view = 'filament.pages.minhas-tarefas';

    protected static ?string $title = 'Minhas Tarefas';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = -1;

    protected static ?string $slug = 'minhas-tarefas';

    public ?int $year = null;
    public ?string $month = null;
    public ?string $statusFilter = null;
    public ?int $projetoFilter = null;

    public function mount(): void
    {
        $this->year = (int) request()->query('year', now()->year);
        $this->month = request()->query('month');
        $this->statusFilter = request()->query('status');
        $this->projetoFilter = request()->query('projeto_id') ? (int) request()->query('projeto_id') : null;
    }

    private function getPoloIdsPermitidos(): ?array
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        if ($user->isCoordenadorPolo() || $user->isDiretorOperacoes()) {
            $poloIds = $user->polos->pluck('id');
            $geralIds = Polo::where('is_geral', true)->pluck('id');
            return $poloIds->merge($geralIds)->unique()->values()->all();
        }

        return null;
    }

    public function getTarefasMensal(): array
    {
        $query = Tarefa::query()
            ->with([
                'meta.projeto',
                'polo',
                'responsaveis',
                'ocorrencias',
                'realizacoes',
                'validadoPorUser',
            ]);

        $poloIdsPermitidos = $this->getPoloIdsPermitidos();
        if ($poloIdsPermitidos !== null) {
            $query->whereIn('polo_id', $poloIdsPermitidos);
        }

        if ($this->projetoFilter) {
            $query->whereHas('meta', fn ($q) => $q->where('projeto_id', $this->projetoFilter));
        }

        $tarefas = $query->get();

        $buckets = [];
        $semData = [];

        foreach ($tarefas as $tarefa) {
            $meta = $tarefa->meta;
            $projeto = $meta?->projeto;
            if (!$projeto) {
                continue;
            }

            $inicio = $tarefa->data_inicio;
            $fim = $tarefa->data_fim ?? $tarefa->data_inicio;
            $ocorrencias = $tarefa->ocorrencias;

            $mesesTarefa = [];
            $prazoPorMes = [];
            $ocorrenciaPorMes = [];

            if ($ocorrencias->isNotEmpty()) {
                $ocorrencias
                    ->groupBy(fn ($o) => $o->data_fim->format('Y-m'))
                    ->each(function ($items, $key) use (&$mesesTarefa, &$prazoPorMes, &$ocorrenciaPorMes) {
                        $mesesTarefa[] = $key;
                        $prazoPorMes[$key] = $items->min('data_fim');
                        $ocorrencia = $items->sortBy('data_fim')->first();
                        $ocorrenciaPorMes[$key] = $ocorrencia;
                    });
            } elseif ($fim) {
                $key = $fim->format('Y-m');
                $mesesTarefa[] = $key;
                $prazoPorMes[$key] = $fim;
            } elseif ($inicio) {
                $key = $inicio->format('Y-m');
                $mesesTarefa[] = $key;
                $prazoPorMes[$key] = $inicio;
            }

            $responsaveis = $tarefa->responsaveis->pluck('name')->filter()->values();
            $responsavelTexto = $responsaveis->isNotEmpty()
                ? $responsaveis->join(', ')
                : ($tarefa->responsavel ?? '-');

            $temHistorico = $tarefa->realizacoes->count() > 0 || $tarefa->observacoes || $tarefa->validadoPorUser;

            if (empty($mesesTarefa)) {
                $semData[] = [
                    'id' => $tarefa->id,
                    'meta' => $meta->descricao,
                    'descricao' => $tarefa->descricao,
                    'prazo' => null,
                    'responsavel' => $responsavelTexto ?: '-',
                    'polo' => $tarefa->polo?->nome ?? 'Geral',
                    'status' => $tarefa->status,
                    'status_label' => $tarefa->status_label,
                    'status_color' => $tarefa->status_color,
                    'status_normalizado' => $tarefa->getStatusNormalizado(),
                    'tem_historico' => $temHistorico,
                    'ocorrencia_id' => null,
                    'projeto_nome' => $projeto->nome,
                    'projeto_id' => $projeto->id,
                ];
                continue;
            }

            foreach (array_unique($mesesTarefa) as $key) {
                $ocorrencia = $ocorrenciaPorMes[$key] ?? null;
                $status = $ocorrencia ? $ocorrencia->status : $tarefa->status;
                $statusLabel = $ocorrencia ? $ocorrencia->status_label : $tarefa->status_label;
                $statusColor = $ocorrencia ? $ocorrencia->status_color : $tarefa->status_color;
                $statusNormalizado = $ocorrencia ? $ocorrencia->getStatusNormalizado() : $tarefa->getStatusNormalizado();

                $buckets[$key][] = [
                    'id' => $tarefa->id,
                    'meta' => $meta->descricao,
                    'descricao' => $tarefa->descricao,
                    'prazo' => $prazoPorMes[$key] ?? null,
                    'responsavel' => $responsavelTexto ?: '-',
                    'polo' => $tarefa->polo?->nome ?? 'Geral',
                    'status' => $status,
                    'status_label' => $statusLabel,
                    'status_color' => $statusColor,
                    'status_normalizado' => $statusNormalizado,
                    'tem_historico' => $temHistorico,
                    'ocorrencia_id' => $ocorrencia?->id,
                    'projeto_nome' => $projeto->nome,
                    'projeto_id' => $projeto->id,
                ];
            }
        }

        // Filter by year
        if ($this->year) {
            $prefix = (string) $this->year;
            $buckets = array_filter($buckets, fn ($_, $key) => str_starts_with($key, $prefix), ARRAY_FILTER_USE_BOTH);
            $semData = $this->year == now()->year ? $semData : [];
        }

        // Filter by month
        if ($this->month) {
            $buckets = array_filter($buckets, fn ($_, $key) => $key === $this->month, ARRAY_FILTER_USE_BOTH);
            $semData = [];
        }

        // Filter by status
        if ($this->statusFilter) {
            $statusFilter = $this->statusFilter;
            $buckets = array_map(function ($items) use ($statusFilter) {
                return array_values(array_filter($items, fn ($t) => ($t['status_normalizado'] ?? '') === $statusFilter));
            }, $buckets);
            $buckets = array_filter($buckets, fn ($items) => !empty($items));
            $semData = array_values(array_filter($semData, fn ($t) => ($t['status_normalizado'] ?? '') === $statusFilter));
        }

        // Sort by key (year-month)
        ksort($buckets);

        $resultado = collect($buckets)
            ->map(function ($items, $key) {
                $label = Carbon::createFromFormat('Y-m', $key)
                    ->locale('pt_BR')
                    ->translatedFormat('F Y');

                return [
                    'label' => $label,
                    'items' => collect($items)->sortBy('prazo')->values(),
                ];
            })
            ->values()
            ->all();

        if (!$this->month && !empty($semData)) {
            $resultado[] = [
                'label' => 'Sem data',
                'items' => collect($semData),
            ];
        }

        return $resultado;
    }

    public function getAnosOptions(): array
    {
        $currentYear = now()->year;
        $anos = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $anos[$y] = (string) $y;
        }
        return $anos;
    }

    public function getMesesOptions(): array
    {
        $ano = $this->year ?? now()->year;
        $meses = [];
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($ano, $m, 1);
            $meses[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }
        return $meses;
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

    public function getStatusOptions(): array
    {
        return [
            'pendente' => 'Pendente',
            'em_execucao' => 'Em Execução',
            'em_analise' => 'Em Análise',
            'devolvido' => 'Devolvido',
            'realizado' => 'Realizado',
            'com_ressalvas' => 'Com Ressalvas',
        ];
    }

    public function getResumo(): array
    {
        $mensal = $this->getTarefasMensal();
        $total = 0;
        $pendentes = 0;
        $emAnalise = 0;
        $realizadas = 0;

        foreach ($mensal as $mes) {
            foreach ($mes['items'] as $tarefa) {
                $total++;
                $sn = $tarefa['status_normalizado'] ?? 'pendente';
                if (in_array($sn, ['pendente', 'em_execucao', 'devolvido'])) {
                    $pendentes++;
                } elseif ($sn === 'em_analise') {
                    $emAnalise++;
                } elseif (in_array($sn, ['realizado', 'com_ressalvas'])) {
                    $realizadas++;
                }
            }
        }

        return [
            'total' => $total,
            'pendentes' => $pendentes,
            'em_analise' => $emAnalise,
            'realizadas' => $realizadas,
        ];
    }

    public function realizarTarefaAction(): Action
    {
        return Action::make('realizarTarefa')
            ->label('Enviar para Análise')
            ->modalHeading('Enviar tarefa para análise')
            ->modalDescription('A tarefa será enviada para validação do Diretor de Projetos / Admin.')
            ->modalSubmitActionLabel('Enviar')
            ->visible(function (): bool {
                $user = auth()->user();
                return $user && ($user->isSuperAdmin() || $user->isCoordenadorPolo() || $user->isDiretorOperacoes());
            })
            ->form([
                Forms\Components\Hidden::make('tarefa_id')->required(),
                Forms\Components\Hidden::make('tarefa_ocorrencia_id'),
                Forms\Components\Textarea::make('comentario')
                    ->label('Comentário')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
                'tarefa_ocorrencia_id' => $arguments['tarefa_ocorrencia_id'] ?? null,
            ])
            ->action(function (array $data) {
                $tarefa = Tarefa::findOrFail($data['tarefa_id']);
                $ocorrenciaId = $data['tarefa_ocorrencia_id'] ?? null;
                $ocorrencia = $ocorrenciaId ? $tarefa->ocorrencias()->find($ocorrenciaId) : null;
                $user = auth()->user();

                if (!$user || (!$user->isSuperAdmin() && !$user->isCoordenadorPolo() && !$user->isDiretorOperacoes())) {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Apenas Coordenador de Polo ou Diretor de Operações podem enviar para análise.')
                        ->danger()
                        ->send();
                    return;
                }

                TarefaRealizacao::create([
                    'tarefa_id' => $tarefa->id,
                    'tarefa_ocorrencia_id' => $ocorrencia?->id,
                    'user_id' => $user->id,
                    'comentario' => $data['comentario'],
                ]);

                if ($ocorrencia) {
                    $ocorrencia->update(['status' => 'em_analise']);
                } else {
                    $tarefa->update(['status' => 'em_analise']);
                }

                Notification::make()
                    ->title('Tarefa enviada para análise')
                    ->body('Aguardando validação do Diretor de Projetos / Admin.')
                    ->success()
                    ->send();

                $destinatarios = NotificacaoCentral::adminsEDiretoresProjetos();
                if ($destinatarios->isNotEmpty()) {
                    LaravelNotification::send($destinatarios, new TarefaEnviadaParaAnalise($tarefa, $user));
                }
            });
    }

    public function analisarTarefaAction(): Action
    {
        return Action::make('analisarTarefa')
            ->label('Analisar')
            ->modalHeading('Análise da Tarefa')
            ->modalSubmitActionLabel('Confirmar')
            ->modalWidth('lg')
            ->form([
                Forms\Components\Hidden::make('tarefa_id')->required(),
                Forms\Components\Hidden::make('tarefa_ocorrencia_id'),

                Forms\Components\Placeholder::make('info_envio')
                    ->label('Enviado por')
                    ->content(function (Forms\Get $get) {
                        $tarefa = Tarefa::with('realizacoes.user')->find($get('tarefa_id'));
                        $realizacao = $tarefa?->realizacoes?->last();
                        if (!$realizacao) return 'N/A';
                        $data = $realizacao->created_at?->format('d/m/Y H:i') ?? '';
                        return $realizacao->user?->name . ' em ' . $data;
                    }),

                Forms\Components\Placeholder::make('comentario_envio')
                    ->label('Comentário enviado')
                    ->content(function (Forms\Get $get) {
                        $tarefa = Tarefa::with('realizacoes')->find($get('tarefa_id'));
                        $realizacao = $tarefa?->realizacoes?->last();
                        return $realizacao?->comentario ?? 'Nenhum comentário.';
                    }),

                Forms\Components\Select::make('decisao')
                    ->label('Decisão')
                    ->options([
                        'aprovar' => 'Aprovar',
                        'aprovar_ressalva' => 'Aprovar com ressalvas',
                        'rejeitar' => 'Devolver para ajuste',
                    ])
                    ->required()
                    ->native(false)
                    ->reactive(),

                Forms\Components\Textarea::make('observacao')
                    ->label('Observação')
                    ->rows(3)
                    ->maxLength(2000)
                    ->required(fn (Forms\Get $get): bool => $get('decisao') === 'aprovar_ressalva')
                    ->visible(fn (Forms\Get $get): bool => in_array($get('decisao'), ['aprovar', 'aprovar_ressalva'], true)),

                Forms\Components\Textarea::make('motivo')
                    ->label('Motivo da devolução')
                    ->rows(3)
                    ->maxLength(2000)
                    ->required(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar')
                    ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar'),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
                'tarefa_ocorrencia_id' => $arguments['tarefa_ocorrencia_id'] ?? null,
            ])
            ->action(function (array $data) {
                $tarefa = Tarefa::findOrFail($data['tarefa_id']);
                $ocorrenciaId = $data['tarefa_ocorrencia_id'] ?? null;
                $ocorrencia = $ocorrenciaId ? $tarefa->ocorrencias()->find($ocorrenciaId) : null;
                $user = auth()->user();

                if (!$user->isAdminGeral() && !$user->isDiretorProjetos()) {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Apenas Diretor de Projetos ou Admin podem analisar.')
                        ->danger()
                        ->send();
                    return;
                }

                if (in_array($data['decisao'], ['aprovar', 'aprovar_ressalva'], true)) {
                    $status = $data['decisao'] === 'aprovar_ressalva' ? 'com_ressalvas' : 'realizado';
                    $updateData = [
                        'status' => $status,
                        'comprovacao_validada' => true,
                        'validado_por' => $user->id,
                        'validado_em' => now(),
                    ];

                    if (!empty($data['observacao'])) {
                        $obs = $tarefa->observacoes;
                        $prefix = $data['decisao'] === 'aprovar_ressalva'
                            ? '[Aprovado com ressalvas por ' . $user->name . '] '
                            : '[Validado por ' . $user->name . '] ';
                        $updateData['observacoes'] = ($obs ? $obs . "\n" : '') . $prefix . $data['observacao'];
                    }

                    if ($ocorrencia) {
                        $ocorrencia->update($updateData);
                    } else {
                        $tarefa->update($updateData);
                    }

                    $notification = Notification::make()
                        ->title($data['decisao'] === 'aprovar_ressalva'
                            ? 'Tarefa aprovada com ressalvas'
                            : 'Tarefa aprovada com sucesso');

                    if ($data['decisao'] === 'aprovar_ressalva') {
                        $notification->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();

                    $enviadoPor = NotificacaoCentral::ultimoEnvioTarefa($tarefa);
                    if ($enviadoPor && $enviadoPor->id !== $user->id) {
                        $enviadoPor->notify(new TarefaAprovada($tarefa, $user));
                    }
                } else {
                    if ($ocorrencia) {
                        $obs = $ocorrencia->observacoes;
                        $ocorrencia->update([
                            'status' => 'devolvido',
                            'comprovacao_validada' => false,
                            'validado_por' => null,
                            'validado_em' => null,
                            'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                        ]);
                    } else {
                        $obs = $tarefa->observacoes;
                        $tarefa->update([
                            'status' => 'devolvido',
                            'comprovacao_validada' => false,
                            'validado_por' => null,
                            'validado_em' => null,
                            'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                        ]);
                    }

                    Notification::make()
                        ->title('Tarefa devolvida para ajuste')
                        ->warning()
                        ->send();

                    $enviadoPor = NotificacaoCentral::ultimoEnvioTarefa($tarefa);
                    if ($enviadoPor && $enviadoPor->id !== $user->id) {
                        $enviadoPor->notify(new TarefaDevolvida($tarefa, $user, $data['motivo']));
                    }
                }
            });
    }

    public function historicoTarefaAction(): Action
    {
        return Action::make('historicoTarefa')
            ->label('Histórico')
            ->modalHeading('Histórico da Tarefa')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->form([
                Forms\Components\Hidden::make('tarefa_id')->required(),
                Forms\Components\Hidden::make('tarefa_ocorrencia_id'),

                Forms\Components\Placeholder::make('historico_content')
                    ->label('')
                    ->content(function (Forms\Get $get) {
                        $tarefa = Tarefa::with(['realizacoes.user', 'validadoPorUser', 'ocorrencias.validadoPorUser'])->find($get('tarefa_id'));
                        if (!$tarefa) return new HtmlString('<p class="text-gray-500">Nenhum registro encontrado.</p>');

                        $html = '<div class="space-y-4">';

                        $ocorrenciaId = $get('tarefa_ocorrencia_id');
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
                            foreach ($linhas as $linha) {
                                $linha = trim($linha);
                                if (empty($linha)) continue;

                                if (str_starts_with($linha, '[Devolvido por ') || str_starts_with($linha, '[Rejeitado por ')) {
                                    $html .= '<div class="border-l-4 border-red-400 bg-red-50 dark:bg-red-950 dark:border-red-600 rounded-r-lg p-3">';
                                    $html .= '<div class="flex items-center gap-2 mb-1">';
                                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Devolvido</span>';
                                    $html .= '</div>';
                                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                                    $html .= '</div>';
                                } elseif (str_starts_with($linha, '[Aprovado com ressalvas por ')) {
                                    $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                                    $html .= '<div class="flex items-center gap-2 mb-1">';
                                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Validado com ressalva</span>';
                                    $html .= '</div>';
                                    $html .= '<p class="text-sm text-gray-700 dark:text-gray-300">' . e($linha) . '</p>';
                                    $html .= '</div>';
                                } elseif (str_starts_with($linha, '[Validado por ') || str_starts_with($linha, '[Validação]')) {
                                    $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                                    $html .= '<div class="flex items-center gap-2 mb-1">';
                                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aprovado</span>';
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
                            $oc = $tarefa->ocorrencias->firstWhere('id', (int) $ocorrenciaId);
                            $validadoPor = $oc?->validadoPorUser;
                            $validadoEm = $oc?->validado_em;
                            $statusValidacao = $oc?->status;
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
                    }),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
                'tarefa_ocorrencia_id' => $arguments['tarefa_ocorrencia_id'] ?? null,
            ]);
    }
}
