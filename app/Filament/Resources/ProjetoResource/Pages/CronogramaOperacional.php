<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
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
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Illuminate\Support\HtmlString;

class CronogramaOperacional extends Page implements HasActions
{
    use InteractsWithRecord;
    use InteractsWithActions {
        InteractsWithActions::afterActionCalled insteadof InteractsWithRecord;
        InteractsWithActions::configureAction insteadof InteractsWithRecord;
        InteractsWithRecord::getMountedActionFormModel insteadof InteractsWithActions;
    }

    protected static string $resource = ProjetoResource::class;

    protected static string $view = 'filament.resources.projeto-resource.pages.cronograma-operacional';

    protected static ?string $title = 'Cronograma de Metas/Tarefas';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    public ?int $year = null;
    public ?string $month = null;
    public ?int $metaId = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->year = (int) request()->query('year', $this->record->data_inicio?->year ?? now()->year);
        $this->month = request()->query('month');
        $this->metaId = request()->query('meta_id') ? (int) request()->query('meta_id') : null;
        $this->record->load([
            'polos',
            'metas.tarefas.polo',
            'metas.tarefas.responsaveis',
            'metas.tarefas.ocorrencias',
            'metas.tarefas.realizacoes',
            'metas.tarefas.validadoPorUser',
        ]);
    }

    public function getAnos(): array
    {
        $inicio = $this->record->data_inicio?->year ?? now()->year;
        $fim = $this->record->data_encerramento?->year ?? $inicio;

        if ($fim < $inicio) {
            $fim = $inicio;
        }

        return range($inicio, $fim);
    }

    public function getMeses(): array
    {
        $ano = $this->year ?? now()->year;
        $inicioProjeto = $this->record->data_inicio?->copy()->startOfMonth() ?? Carbon::create($ano, 1, 1);
        $fimProjeto = $this->record->data_encerramento?->copy()->startOfMonth() ?? Carbon::create($ano, 12, 1);

        $inicio = Carbon::create($ano, 1, 1)->startOfMonth();
        $fim = Carbon::create($ano, 12, 1)->startOfMonth();

        if ($inicioProjeto->gt($inicio)) {
            $inicio = $inicioProjeto->copy();
        }
        if ($fimProjeto->lt($fim)) {
            $fim = $fimProjeto->copy();
        }

        if ($fim->lt($inicio)) {
            $fim = $inicio->copy();
        }

        $meses = [];
        $cursor = $inicio->copy();
        while ($cursor->lte($fim)) {
            $meses[] = [
                'key' => $cursor->format('Y-m'),
                'label' => mb_strtoupper($cursor->translatedFormat('M')),
            ];
            $cursor->addMonth();
        }

        if ($this->month) {
            $meses = array_values(array_filter($meses, fn ($m) => $m['key'] === $this->month));
        }

        return $meses;
    }

    public function getMesesOptions(): array
    {
        $ano = $this->year ?? now()->year;
        $inicioProjeto = $this->record->data_inicio?->copy()->startOfMonth() ?? Carbon::create($ano, 1, 1);
        $fimProjeto = $this->record->data_encerramento?->copy()->startOfMonth() ?? Carbon::create($ano, 12, 1);

        $inicio = Carbon::create($ano, 1, 1)->startOfMonth();
        $fim = Carbon::create($ano, 12, 1)->startOfMonth();

        if ($inicioProjeto->gt($inicio)) {
            $inicio = $inicioProjeto->copy();
        }
        if ($fimProjeto->lt($fim)) {
            $fim = $fimProjeto->copy();
        }

        if ($fim->lt($inicio)) {
            $fim = $inicio->copy();
        }

        $meses = [];
        $cursor = $inicio->copy();
        while ($cursor->lte($fim)) {
            $meses[$cursor->format('Y-m')] = $cursor->translatedFormat('F Y');
            $cursor->addMonth();
        }

        return $meses;
    }

    public function getLinhas(): array
    {
        $meses = $this->getMeses();
        $mesKeys = array_map(fn ($m) => $m['key'], $meses);

        $metas = $this->record->metas;
        if ($this->metaId) {
            $metas = $metas->where('id', $this->metaId);
        }

        return $metas->map(function ($meta) use ($mesKeys) {
            $tarefas = $meta->tarefas->sortBy('data_fim')->values()->map(function ($tarefa) use ($mesKeys) {
                $marcacoes = array_fill_keys($mesKeys, '');

                $inicio = $tarefa->data_inicio;
                $fim = $tarefa->data_fim ?? $tarefa->data_inicio;
                $ocorrencias = $tarefa->ocorrencias;

                if ($ocorrencias->isNotEmpty()) {
                    foreach ($ocorrencias as $ocorrencia) {
                        $key = $ocorrencia->data_fim->format('Y-m');
                        if (array_key_exists($key, $marcacoes)) {
                            $marcacoes[$key] = 'X';
                        }
                    }
                } elseif ($inicio && $fim) {
                    $cursor = $inicio->copy()->startOfMonth();
                    $end = $fim->copy()->startOfMonth();
                    while ($cursor->lte($end)) {
                        $key = $cursor->format('Y-m');
                        if (array_key_exists($key, $marcacoes)) {
                            $marcacoes[$key] = 'X';
                        }
                        $cursor->addMonth();
                    }
                } elseif ($fim) {
                    $key = $fim->format('Y-m');
                    if (array_key_exists($key, $marcacoes)) {
                        $marcacoes[$key] = 'X';
                    }
                }

                $responsaveis = $tarefa->responsaveis->pluck('name')->filter()->values();
                $responsavelTexto = $responsaveis->isNotEmpty()
                    ? $responsaveis->join(', ')
                    : ($tarefa->responsavel ?? '-');

                $periodo = $inicio && $fim
                    ? $inicio->format('d/m/Y') . ' - ' . $fim->format('d/m/Y')
                    : ($fim?->format('d/m/Y') ?? '-');

                if ($ocorrencias->isNotEmpty()) {
                    $periodo = $ocorrencias->min('data_fim')->format('d/m/Y')
                        . ' - ' . $ocorrencias->max('data_fim')->format('d/m/Y');
                }

                return [
                    'descricao' => $tarefa->descricao,
                    'marcacoes' => $marcacoes,
                    'periodo' => $periodo,
                    'responsavel' => $responsavelTexto ?: '-',
                    'como_fazer' => $tarefa->como_fazer ?? '-',
                    'polo' => $tarefa->polo?->nome ?? 'Geral',
                ];
            });

            return [
                'meta' => $meta->descricao,
                'tarefas' => $tarefas,
            ];
        })->toArray();
    }

    public function getTarefasMensal(): array
    {
        $meses = $this->getMeses();
        $mesKeys = array_map(fn ($m) => $m['key'], $meses);
        $buckets = array_fill_keys($mesKeys, []);
        $semData = [];

        $metas = $this->record->metas;
        if ($this->metaId) {
            $metas = $metas->where('id', $this->metaId);
        }

        foreach ($metas as $meta) {
            foreach ($meta->tarefas as $tarefa) {
                $inicio = $tarefa->data_inicio;
                $fim = $tarefa->data_fim ?? $tarefa->data_inicio;
                $ocorrencias = $tarefa->ocorrencias;

                $mesesTarefa = [];
                $prazoPorMes = [];

                if ($ocorrencias->isNotEmpty()) {
                    $ocorrencias
                        ->groupBy(fn ($o) => $o->data_fim->format('Y-m'))
                        ->each(function ($items, $key) use (&$mesesTarefa, &$prazoPorMes) {
                            $mesesTarefa[] = $key;
                            $prazoPorMes[$key] = $items->min('data_fim');
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
                    ];
                    continue;
                }

                foreach (array_unique($mesesTarefa) as $key) {
                    if (array_key_exists($key, $buckets)) {
                        $buckets[$key][] = [
                            'id' => $tarefa->id,
                            'meta' => $meta->descricao,
                            'descricao' => $tarefa->descricao,
                            'prazo' => $prazoPorMes[$key] ?? null,
                            'responsavel' => $responsavelTexto ?: '-',
                            'polo' => $tarefa->polo?->nome ?? 'Geral',
                            'status' => $tarefa->status,
                            'status_label' => $tarefa->status_label,
                            'status_color' => $tarefa->status_color,
                            'status_normalizado' => $tarefa->getStatusNormalizado(),
                            'tem_historico' => $temHistorico,
                        ];
                    }
                }
            }
        }

        if ($this->month) {
            $buckets = array_filter($buckets, fn ($_, $key) => $key === $this->month, ARRAY_FILTER_USE_BOTH);
        }

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

    public function getMetasOptions(): array
    {
        return $this->record->metas
            ->mapWithKeys(fn ($meta) => [$meta->id => $meta->numero . ' - ' . $meta->descricao])
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function realizarTarefaAction(): Action
    {
        return Action::make('realizarTarefa')
            ->label('Enviar para Análise')
            ->modalHeading('Enviar tarefa para análise')
            ->modalDescription('A tarefa será enviada para validação do Diretor de Projetos / Admin.')
            ->modalSubmitActionLabel('Enviar')
            ->form([
                Forms\Components\Hidden::make('tarefa_id')->required(),
                Forms\Components\Textarea::make('comentario')
                    ->label('Comentário')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
            ])
            ->action(function (array $data) {
                $tarefa = Tarefa::findOrFail($data['tarefa_id']);
                $user = auth()->user();

                TarefaRealizacao::create([
                    'tarefa_id' => $tarefa->id,
                    'user_id' => $user->id,
                    'comentario' => $data['comentario'],
                ]);

                $tarefa->update(['status' => 'em_analise']);

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
                        'rejeitar' => 'Devolver para ajuste',
                    ])
                    ->required()
                    ->native(false)
                    ->reactive(),

                Forms\Components\Textarea::make('observacao')
                    ->label('Observação')
                    ->rows(3)
                    ->maxLength(2000)
                    ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'aprovar'),

                Forms\Components\Textarea::make('motivo')
                    ->label('Motivo da devolução')
                    ->rows(3)
                    ->maxLength(2000)
                    ->required(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar')
                    ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar'),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
            ])
            ->action(function (array $data) {
                $tarefa = Tarefa::findOrFail($data['tarefa_id']);
                $user = auth()->user();

                if (!$user->isAdminGeral() && !$user->isDiretorProjetos()) {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Apenas Diretor de Projetos ou Admin podem analisar.')
                        ->danger()
                        ->send();
                    return;
                }

                if ($data['decisao'] === 'aprovar') {
                    $updateData = [
                        'status' => 'realizado',
                        'comprovacao_validada' => true,
                        'validado_por' => $user->id,
                        'validado_em' => now(),
                    ];

                    if (!empty($data['observacao'])) {
                        $obs = $tarefa->observacoes;
                        $updateData['observacoes'] = ($obs ? $obs . "\n" : '') . '[Validado por ' . $user->name . '] ' . $data['observacao'];
                    }

                    $tarefa->update($updateData);

                    Notification::make()
                        ->title('Tarefa aprovada com sucesso')
                        ->success()
                        ->send();

                    $enviadoPor = NotificacaoCentral::ultimoEnvioTarefa($tarefa);
                    if ($enviadoPor && $enviadoPor->id !== $user->id) {
                        $enviadoPor->notify(new TarefaAprovada($tarefa, $user));
                    }
                } else {
                    $obs = $tarefa->observacoes;
                    $tarefa->update([
                        'status' => 'devolvido',
                        'comprovacao_validada' => false,
                        'validado_por' => null,
                        'validado_em' => null,
                        'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                    ]);

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

                Forms\Components\Placeholder::make('historico_content')
                    ->label('')
                    ->content(function (Forms\Get $get) {
                        $tarefa = Tarefa::with(['realizacoes.user', 'validadoPorUser'])->find($get('tarefa_id'));
                        if (!$tarefa) return new HtmlString('<p class="text-gray-500">Nenhum registro encontrado.</p>');

                        $html = '<div class="space-y-4">';

                        $realizacoes = $tarefa->realizacoes->sortBy('created_at');
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

                        if ($tarefa->observacoes) {
                            $linhas = array_filter(explode("\n", $tarefa->observacoes));
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

                        if ($tarefa->validadoPorUser && $tarefa->validado_em) {
                            $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                            $html .= '<div class="flex items-center gap-2">';
                            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Aprovado</span>';
                            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-white">' . e($tarefa->validadoPorUser->name) . '</span>';
                            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $tarefa->validado_em->format('d/m/Y H:i') . '</span>';
                            $html .= '</div>';
                            $html .= '</div>';
                        }

                        if ($realizacoes->isEmpty() && !$tarefa->observacoes && !$tarefa->validadoPorUser) {
                            $html .= '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhum registro no histórico.</p>';
                        }

                        $html .= '</div>';
                        return new HtmlString($html);
                    }),
            ])
            ->fillForm(fn (array $arguments) => [
                'tarefa_id' => $arguments['tarefa_id'] ?? null,
            ]);
    }
}
