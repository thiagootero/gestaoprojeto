<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use App\Models\EtapaPrestacao;
use App\Models\PrestacaoRealizacao;
use App\Notifications\PrestacaoAprovada;
use App\Notifications\PrestacaoDevolvida;
use App\Notifications\PrestacaoEnviadaParaAnalise;
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

class CronogramaPrestacaoContas extends Page implements HasActions
{
    use InteractsWithRecord;
    use InteractsWithActions {
        InteractsWithActions::afterActionCalled insteadof InteractsWithRecord;
        InteractsWithActions::configureAction insteadof InteractsWithRecord;
        InteractsWithRecord::getMountedActionFormModel insteadof InteractsWithActions;
    }

    protected static string $resource = ProjetoResource::class;

    protected static string $view = 'filament.resources.projeto-resource.pages.cronograma-prestacao-contas';

    protected static ?string $title = 'Cronograma Prestação de Contas';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isDiretorProjetos()
            || $user->isCoordenadorFinanceiro();
    }

    public function getBreadcrumbs(): array
    {
        return [
            ProjetoResource::getUrl('index') => 'Projetos',
            ProjetoResource::getUrl('view', ['record' => $this->record]) => $this->record->nome,
            static::getTitle(),
        ];
    }

    public ?string $month = null;
    public ?string $origem = null;
    public ?int $financiadorId = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->month = request()->query('month');
        $this->origem = request()->query('origem');
        $this->financiadorId = request()->query('financiador_id') ? (int) request()->query('financiador_id') : null;
        $this->record->load([
            'etapasPrestacao.projetoFinanciador.financiador',
            'etapasPrestacao.validadoPorUser',
            'etapasPrestacao.realizacoes',
            'contratos.financiador',
        ]);
    }

    private function getEtapasFiltradas()
    {
        return $this->record->etapasPrestacao->filter(function ($etapa) {
            if ($this->origem === 'interna' && $etapa->origem !== 'interna') {
                return false;
            }
            if ($this->origem === 'financiador' && $etapa->origem !== 'financiador') {
                return false;
            }
            if ($this->financiadorId) {
                $finId = $etapa->projetoFinanciador?->financiador_id;
                if ($finId !== $this->financiadorId) {
                    return false;
                }
            }
            if ($this->month) {
                if (!$etapa->data_limite) {
                    return false;
                }
                return $etapa->data_limite->format('Y-m') === $this->month;
            }
            return true;
        });
    }

    public function getPrestacaoResumo(): array
    {
        $etapas = $this->getEtapasFiltradas();

        $internasQual = $etapas
            ->where('origem', 'interna')
            ->where('tipo', 'qualitativa')
            ->sortBy('data_limite');

        $internasQuant = $etapas
            ->where('origem', 'interna')
            ->where('tipo', 'financeira')
            ->sortBy('data_limite');

        $financiadores = $etapas
            ->where('origem', 'financiador')
            ->groupBy(function ($etapa) {
                $financiador = $etapa->projetoFinanciador?->financiador;
                return $financiador?->nome ?? 'Financiador';
            })
            ->map(function ($items) {
                $qualitativas = $items->where('tipo', 'qualitativa')->sortBy('data_limite');
                $quantitativas = $items->where('tipo', 'financeira')->sortBy('data_limite');

                return [
                    'qualitativas' => $qualitativas->values(),
                    'quantitativas' => $quantitativas->values(),
                ];
            });

        return [
            'internas' => [
                'qualitativas' => $internasQual->values(),
                'quantitativas' => $internasQuant->values(),
            ],
            'financiadores' => $financiadores,
        ];
    }

    public function getPrestacaoMensal(): array
    {
        $etapas = $this->getEtapasFiltradas();

        return $etapas
            ->sortBy('data_limite')
            ->groupBy(function ($etapa) {
                return $etapa->data_limite?->format('Y-m') ?? 'sem-data';
            })
            ->sortKeys()
            ->map(function ($items, $key) {
                $label = $key === 'sem-data'
                    ? 'Sem data'
                    : Carbon::createFromFormat('Y-m', $key)
                        ->locale('pt_BR')
                        ->translatedFormat('F Y');

                return [
                    'label' => $label,
                    'items' => $items->sortBy('data_limite')->values(),
                ];
            })
            ->when($this->month, function ($collection) {
                return $collection->filter(fn ($m) => $m['label'] !== 'Sem data');
            })
            ->values()
            ->all();
    }

    public function getMesesOptions(): array
    {
        return $this->record->etapasPrestacao
            ->filter(fn ($etapa) => $etapa->data_limite)
            ->sortBy('data_limite')
            ->mapWithKeys(fn ($etapa) => [
                $etapa->data_limite->format('Y-m') => $etapa->data_limite->translatedFormat('F Y'),
            ])
            ->unique()
            ->toArray();
    }

    public function getFinanciadoresOptions(): array
    {
        return $this->record->contratos
            ->mapWithKeys(fn ($contrato) => [$contrato->financiador_id => $contrato->financiador->nome])
            ->toArray();
    }

    public function formatData(?Carbon $data): string
    {
        return $data ? $data->format('d/m/Y') : '-';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function realizarPrestacaoAction(): Action
    {
        return Action::make('realizarPrestacao')
            ->label('Enviar para Análise')
            ->modalHeading('Enviar prestação de contas para análise')
            ->modalDescription(function (): string {
                $user = auth()->user();
                if ($user && ($user->isSuperAdmin() || $user->isDiretorProjetos() || $user->isCoordenadorFinanceiro())) {
                    return 'A prestação será validada imediatamente.';
                }
                return 'A prestação será enviada para validação do Diretor de Operações / Admin.';
            })
            ->modalSubmitActionLabel('Enviar')
            ->form([
                Forms\Components\Hidden::make('etapa_id')->required(),
                Forms\Components\Select::make('decisao_envio')
                    ->label('Envio')
                    ->options([
                        'enviar' => 'Validado',
                        'enviar_ressalva' => 'Validado com ressalva',
                    ])
                    ->required()
                    ->native(false)
                    ->visible(function (): bool {
                        $user = auth()->user();
                        return $user && ($user->isSuperAdmin() || $user->isDiretorProjetos() || $user->isCoordenadorFinanceiro());
                    }),
                Forms\Components\Textarea::make('comentario')
                    ->label('Comentário')
                    ->required()
                    ->rows(4)
                    ->maxLength(2000),
            ])
            ->fillForm(fn (array $arguments) => [
                'etapa_id' => $arguments['etapa_id'] ?? null,
            ])
            ->action(function (array $data) {
                $etapa = EtapaPrestacao::findOrFail($data['etapa_id']);
                $user = auth()->user();

                $autoValidar = $user && ($user->isSuperAdmin() || $user->isDiretorProjetos() || $user->isCoordenadorFinanceiro());

                if ($user->isCoordenadorFinanceiro() && $etapa->tipo !== 'financeira') {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Coordenador financeiro só pode enviar prestações financeiras.')
                        ->danger()
                        ->send();
                    return;
                }

                if ($user->isCoordenadorPolo() && $etapa->tipo !== 'qualitativa') {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Coordenador de polo só pode enviar prestações qualitativas.')
                        ->danger()
                        ->send();
                    return;
                }

                PrestacaoRealizacao::create([
                    'etapa_prestacao_id' => $etapa->id,
                    'user_id' => $user->id,
                    'comentario' => $data['comentario'],
                ]);

                if ($autoValidar) {
                    $decisao = $data['decisao_envio'] ?? 'enviar';
                    $status = $decisao === 'enviar_ressalva' ? 'com_ressalvas' : 'realizado';
                    $updateData = [
                        'status' => $status,
                        'validado_por' => $user->id,
                        'validado_em' => now(),
                    ];

                    if ($decisao === 'enviar_ressalva') {
                        $obs = $etapa->observacoes;
                        $updateData['observacoes'] = ($obs ? $obs . "\n" : '')
                            . '[Validado com ressalva por ' . $user->name . '] ' . $data['comentario'];
                    }

                    $etapa->update($updateData);

                    Notification::make()
                        ->title($decisao === 'enviar_ressalva'
                            ? 'Prestação validada com ressalva'
                            : 'Prestação validada')
                        ->success()
                        ->send();

                    return;
                }

                $etapa->update(['status' => 'em_analise']);

                Notification::make()
                    ->title('Prestação enviada para análise')
                    ->body('Aguardando validação do Diretor de Operações / Admin.')
                    ->success()
                    ->send();

                $destinatarios = NotificacaoCentral::adminsEDiretoresPrestacao();
                if ($destinatarios->isNotEmpty()) {
                    LaravelNotification::send($destinatarios, new PrestacaoEnviadaParaAnalise($etapa, $user));
                }
            });
    }

    public function historicoPrestacaoAction(): Action
    {
        return Action::make('historicoPrestacao')
            ->label('Histórico')
            ->modalHeading('Histórico da Prestação de Contas')
            ->modalWidth('lg')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fechar')
            ->form([
                Forms\Components\Hidden::make('etapa_id')->required(),

                Forms\Components\Placeholder::make('historico_content')
                    ->label('')
                    ->content(function (Forms\Get $get) {
                        $etapa = EtapaPrestacao::with(['realizacoes.user', 'validadoPorUser'])->find($get('etapa_id'));
                        if (!$etapa) return new HtmlString('<p class="text-gray-500">Nenhum registro encontrado.</p>');

                        $html = '<div class="space-y-4">';

                        // Realizações (envios)
                        $realizacoes = $etapa->realizacoes->sortBy('created_at');
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

                        // Observações (rejeições e validações do campo observacoes)
                        if ($etapa->observacoes) {
                            $linhas = array_filter(explode("\n", $etapa->observacoes));
                            foreach ($linhas as $linha) {
                                $linha = trim($linha);
                                if (empty($linha)) continue;

                                if (str_starts_with($linha, '[Rejeitado por ') || str_starts_with($linha, '[Devolvido por ')) {
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
                                    $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Validado</span>';
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

                        // Status atual de validação
                        if ($etapa->validadoPorUser && $etapa->validado_em) {
                            $html .= '<div class="border-l-4 border-green-400 bg-green-50 dark:bg-green-950 dark:border-green-600 rounded-r-lg p-3">';
                            $html .= '<div class="flex items-center gap-2 mb-1">';
                            $html .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">'
                                . ($etapa->status === 'com_ressalvas' ? 'Validado com ressalva' : 'Validado') . '</span>';
                            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-white">' . e($etapa->validadoPorUser->name) . '</span>';
                            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $etapa->validado_em->format('d/m/Y H:i') . '</span>';
                            $html .= '</div>';
                            $html .= '</div>';
                        }

                        // Se não tem nenhum registro
                        if ($realizacoes->isEmpty() && !$etapa->observacoes && !$etapa->validadoPorUser) {
                            $html .= '<p class="text-gray-500 dark:text-gray-400 text-center py-4">Nenhum registro no histórico.</p>';
                        }

                        $html .= '</div>';
                        return new HtmlString($html);
                    }),
            ])
            ->fillForm(fn (array $arguments) => [
                'etapa_id' => $arguments['etapa_id'] ?? null,
            ]);
    }

    public function analisarPrestacaoAction(): Action
    {
        return Action::make('analisarPrestacao')
            ->label('Analisar')
            ->modalHeading('Análise da Prestação de Contas')
            ->modalSubmitActionLabel('Confirmar')
            ->modalWidth('lg')
            ->form([
                Forms\Components\Hidden::make('etapa_id')->required(),

                Forms\Components\Placeholder::make('info_envio')
                    ->label('Enviado por')
                    ->content(function (Forms\Get $get) {
                        $etapa = EtapaPrestacao::with('realizacoes.user')->find($get('etapa_id'));
                        $realizacao = $etapa?->realizacoes?->last();
                        if (!$realizacao) return 'N/A';
                        $data = $realizacao->created_at?->format('d/m/Y H:i') ?? '';
                        return $realizacao->user?->name . ' em ' . $data;
                    }),

                Forms\Components\Placeholder::make('comentario_envio')
                    ->label('Comentário enviado')
                    ->content(function (Forms\Get $get) {
                        $etapa = EtapaPrestacao::with('realizacoes')->find($get('etapa_id'));
                        $realizacao = $etapa?->realizacoes?->last();
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
                'etapa_id' => $arguments['etapa_id'] ?? null,
            ])
            ->action(function (array $data) {
                $etapa = EtapaPrestacao::findOrFail($data['etapa_id']);
                $user = auth()->user();

                if (!$user->isAdminGeral() && !$user->isDiretorOperacoes() && !$user->isDiretorProjetos()) {
                    Notification::make()
                        ->title('Sem permissão')
                        ->body('Apenas Diretor de Operações, Diretor de Projetos ou Admin podem analisar.')
                        ->danger()
                        ->send();
                    return;
                }

                if (in_array($data['decisao'], ['aprovar', 'aprovar_ressalva'], true)) {
                    $status = $data['decisao'] === 'aprovar_ressalva' ? 'com_ressalvas' : 'realizado';
                    $updateData = [
                        'status' => $status,
                        'validado_por' => $user->id,
                        'validado_em' => now(),
                    ];

                    if (!empty($data['observacao'])) {
                        $obs = $etapa->observacoes;
                        $prefix = $data['decisao'] === 'aprovar_ressalva'
                            ? '[Aprovado com ressalvas por ' . $user->name . '] '
                            : '[Validado por ' . $user->name . '] ';
                        $updateData['observacoes'] = ($obs ? $obs . "\n" : '') . $prefix . $data['observacao'];
                    }

                    $etapa->update($updateData);

                    $notification = Notification::make()
                        ->title($data['decisao'] === 'aprovar_ressalva'
                            ? 'Prestação aprovada com ressalvas'
                            : 'Prestação aprovada com sucesso');

                    if ($data['decisao'] === 'aprovar_ressalva') {
                        $notification->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();

                    $enviadoPor = NotificacaoCentral::ultimoEnvioPrestacao($etapa);
                    if ($enviadoPor && $enviadoPor->id !== $user->id) {
                        $enviadoPor->notify(new PrestacaoAprovada($etapa, $user));
                    }
                } else {
                    $obs = $etapa->observacoes;
                    $etapa->update([
                        'status' => 'devolvido',
                        'validado_por' => null,
                        'validado_em' => null,
                        'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                    ]);

                    Notification::make()
                        ->title('Prestação devolvida para ajuste')
                        ->warning()
                        ->send();

                    $enviadoPor = NotificacaoCentral::ultimoEnvioPrestacao($etapa);
                    if ($enviadoPor && $enviadoPor->id !== $user->id) {
                        $enviadoPor->notify(new PrestacaoDevolvida($etapa, $user, $data['motivo']));
                    }
                }
            });
    }
}
