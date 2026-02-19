<?php

namespace App\Filament\Resources\MetaResource\RelationManagers;

use App\Models\Polo;
use App\Models\Tarefa;
use App\Models\User;
use App\Support\RecorrenciaDateGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TarefasRelationManager extends RelationManager
{
    protected static string $relationship = 'tarefas';

    protected static ?string $title = 'Tarefas';

    protected static ?string $modelLabel = 'Tarefa';

    protected static ?string $pluralModelLabel = 'Tarefas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('descricao')
                    ->label('Descrição')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('polo_ids')
                    ->label('Polo')
                    ->options(function (): array {
                        $projeto = $this->getOwnerRecord()?->projeto;
                        if ($projeto) {
                            return $projeto->polos()
                                ->where('polos.ativo', true)
                                ->pluck('polos.nome', 'polos.id')
                                ->toArray();
                        }

                        return Polo::where('ativo', true)->pluck('nome', 'id')->toArray();
                    })
                    ->multiple()
                    ->native()
                    ->helperText('Selecione um ou mais polos. Na edição, as tarefas serão recriadas por polo.')
                    ->afterStateHydrated(function ($state, callable $set, ?Tarefa $record): void {
                        if (!$record) {
                            return;
                        }

                        if ($record->tarefa_grupo_id) {
                            $poloIds = Tarefa::where('tarefa_grupo_id', $record->tarefa_grupo_id)
                                ->pluck('polo_id')
                                ->filter()
                                ->values()
                                ->all();
                            if (!empty($poloIds)) {
                                $set('polo_ids', $poloIds);
                                return;
                            }
                        }

                        if ($record->polo_id) {
                            $set('polo_ids', [$record->polo_id]);
                        }
                    }),

                Forms\Components\Select::make('responsaveis')
                    ->label('Responsáveis')
                    ->relationship('responsaveis', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->visible(false),

                // --- Campos de recorrência (apenas na criação) ---
                Forms\Components\Select::make('recorrencia_tipo')
                    ->label('Recorrência')
                    ->options([
                        'nenhuma' => 'Sem recorrência',
                        'mensal' => 'Mensal',
                        'bimestral' => 'Bimestral',
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual',
                        'personalizado' => 'Datas personalizadas',
                    ])
                    ->native(false)
                    ->default('nenhuma')
                    ->reactive()
                    ->visible(fn (string $operation): bool => in_array($operation, ['create', 'edit'], true)),

                Forms\Components\DatePicker::make('recorrencia_inicio')
                    ->label('Iniciar em')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma'))
                    ->visible(fn (Get $get, string $operation): bool => in_array($operation, ['create', 'edit'], true) && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\TextInput::make('recorrencia_dia')
                    ->label('Dia do mês')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma'))
                    ->visible(fn (Get $get, string $operation): bool => in_array($operation, ['create', 'edit'], true) && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\Toggle::make('ate_final_projeto')
                    ->label('Até o final do projeto')
                    ->default(false)
                    ->reactive()
                    ->visible(fn (Get $get, string $operation): bool => in_array($operation, ['create', 'edit'], true) && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\TextInput::make('recorrencia_repeticoes')
                    ->label('Quantidade de repetições')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma') && !$get('ate_final_projeto'))
                    ->visible(fn (Get $get, string $operation): bool => in_array($operation, ['create', 'edit'], true) && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma') && !$get('ate_final_projeto')),

                Forms\Components\Repeater::make('datas_personalizadas')
                    ->label('Datas')
                    ->schema([
                        Forms\Components\DatePicker::make('data')
                            ->label('Data')
                            ->displayFormat('d/m/Y')
                            ->native()
                            ->required(),
                    ])
                    ->columns(1)
                    ->minItems(1)
                    ->defaultItems(1)
                    ->visible(fn (Get $get, string $operation): bool => in_array($operation, ['create', 'edit'], true) && ($get('recorrencia_tipo') ?? 'nenhuma') === 'personalizado')
                    ->columnSpanFull(),

                // --- Data única (criação sem recorrência OU edição sem ocorrências) ---
                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data Fim/Prazo')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get, string $operation, ?Tarefa $record): bool =>
                        ($operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                        || ($operation === 'edit' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                    )
                    ->visible(fn (Get $get, string $operation, ?Tarefa $record): bool =>
                        ($operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                        || ($operation === 'edit' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                    ),

                Forms\Components\Textarea::make('como_fazer')
                    ->label('Como Fazer')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->modifyQueryUsing(function ($query) {
                return $query->where(function ($q) {
                    $q->whereNull('tarefa_grupo_id')
                        ->orWhereIn('id', function ($sub) {
                            $sub->select(DB::raw('MIN(id)'))
                                ->from('tarefas')
                                ->whereNotNull('tarefa_grupo_id')
                                ->groupBy('tarefa_grupo_id');
                        });
                });
            })
            ->columns([
                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('polo.nome')
                    ->label('Polo')
                    ->badge()
                    ->placeholder('NSA')
                    ->sortable()
                    ->formatStateUsing(function ($state, Tarefa $record): string {
                        if (!$record->tarefa_grupo_id) {
                            return $record->polo?->nome ?? 'Geral';
                        }

                        $polos = Tarefa::where('tarefa_grupo_id', $record->tarefa_grupo_id)
                            ->with('polo')
                            ->get()
                            ->map(fn (Tarefa $t) => $t->polo?->nome)
                            ->filter()
                            ->unique()
                            ->values();

                        return $polos->isNotEmpty()
                            ? $polos->join(', ')
                            : 'Geral';
                    }),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Tarefa $record): string =>
                        $record->atrasada ? 'danger' : ($record->vencendo ? 'warning' : 'gray')
                    ),

                Tables\Columns\IconColumn::make('comprovacao_validada')
                    ->label('Validada')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->defaultSort('numero', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('polo_id')
                    ->label('Polo')
                    ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id')),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'em_execucao' => 'Em Execução',
                        'em_analise' => 'Em Análise',
                        'devolvido' => 'Devolvido para ajuste',
                        'com_ressalvas' => 'Validado com ressalva',
                        'realizado' => 'Realizado',
                    ]),
                Tables\Filters\TernaryFilter::make('comprovacao_validada')
                    ->label('Validada'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $poloIds = $data['polo_ids'] ?? [];
                        $responsaveis = $data['responsaveis'] ?? [];
                        $grupoId = (string) Str::uuid();
                        $recorrenciaTipo = $data['recorrencia_tipo'] ?? 'nenhuma';
                        $recorrenciaDia = $data['recorrencia_dia'] ?? null;
                        $recorrenciaInicio = $data['recorrencia_inicio'] ?? null;
                        $recorrenciaRepeticoes = $data['recorrencia_repeticoes'] ?? null;
                        $ateFinalProjeto = $data['ate_final_projeto'] ?? false;
                        $datasPersonalizadas = $data['datas_personalizadas'] ?? [];
                        $recorrenciaPayload = [
                            'recorrencia_tipo' => $recorrenciaTipo,
                            'recorrencia_inicio' => $recorrenciaInicio,
                            'recorrencia_dia' => $recorrenciaDia,
                            'recorrencia_repeticoes' => $recorrenciaRepeticoes,
                            'ate_final_projeto' => (bool) $ateFinalProjeto,
                            'datas_personalizadas' => $recorrenciaTipo === 'personalizado' ? $datasPersonalizadas : null,
                        ];
                        unset(
                            $data['polo_ids'],
                            $data['responsaveis'],
                            $data['recorrencia_tipo'],
                            $data['recorrencia_dia'],
                            $data['recorrencia_inicio'],
                            $data['recorrencia_repeticoes'],
                            $data['ate_final_projeto'],
                            $data['datas_personalizadas']
                        );
                        $data['meta_id'] = $this->getOwnerRecord()->id;
                        $data['tarefa_grupo_id'] = $grupoId;
                        $data = array_merge($data, $recorrenciaPayload);

                        $datas = [];
                        if (RecorrenciaDateGenerator::isFrequenciaPeriodica($recorrenciaTipo)) {
                            $inicio = $recorrenciaInicio ?? now()->toDateString();
                            $dia = (int) $recorrenciaDia;

                            if ($ateFinalProjeto) {
                                $dataEncerramento = $this->getOwnerRecord()->projeto->data_encerramento;
                                if ($dataEncerramento) {
                                    $datas = RecorrenciaDateGenerator::gerarAteData(
                                        $recorrenciaTipo,
                                        $inicio,
                                        $dia,
                                        $dataEncerramento
                                    );
                                }
                            } else {
                                $datas = RecorrenciaDateGenerator::gerar(
                                    $recorrenciaTipo,
                                    $inicio,
                                    $dia,
                                    (int) $recorrenciaRepeticoes
                                );
                            }
                        } elseif ($recorrenciaTipo === 'personalizado' && !empty($datasPersonalizadas)) {
                            $datas = collect($datasPersonalizadas)
                                ->pluck('data')
                                ->filter()
                                ->map(fn ($d) => \Carbon\Carbon::parse($d))
                                ->sort()
                                ->values()
                                ->all();
                        }

                        $createTarefa = function (array $payload) use ($datas, $responsaveis): Tarefa {
                            if (!empty($datas)) {
                                $payload['data_fim'] = $datas[0];
                            }
                            $created = Tarefa::create($payload);
                            if (!empty($datas)) {
                                $created->ocorrencias()->createMany(
                                    collect($datas)->map(fn ($d) => ['data_fim' => $d])->all()
                                );
                            }
                            if (!empty($responsaveis)) {
                                $created->responsaveis()->sync($responsaveis);
                            }
                            return $created;
                        };

                        if (empty($poloIds)) {
                            return $createTarefa($data);
                        }

                        $primeira = null;
                        foreach ($poloIds as $poloId) {
                            $payload = $data;
                            $payload['polo_id'] = $poloId;
                            $created = $createTarefa($payload);
                            $primeira ??= $created;
                        }

                        return $primeira;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->using(function (Tarefa $record, array $data): Tarefa {
                        $poloIds = $data['polo_ids'] ?? [];
                        $responsaveis = $data['responsaveis'] ?? [];
                        $recorrenciaTipo = $data['recorrencia_tipo'] ?? 'nenhuma';
                        $recorrenciaDia = $data['recorrencia_dia'] ?? null;
                        $recorrenciaInicio = $data['recorrencia_inicio'] ?? null;
                        $recorrenciaRepeticoes = $data['recorrencia_repeticoes'] ?? null;
                        $ateFinalProjeto = $data['ate_final_projeto'] ?? false;
                        $datasPersonalizadas = $data['datas_personalizadas'] ?? [];

                        unset(
                            $data['polo_ids'],
                            $data['responsaveis'],
                            $data['recorrencia_tipo'],
                            $data['recorrencia_dia'],
                            $data['recorrencia_inicio'],
                            $data['recorrencia_repeticoes'],
                            $data['ate_final_projeto'],
                            $data['datas_personalizadas']
                        );

                        $grupoId = $record->tarefa_grupo_id ?: (string) Str::uuid();
                        if (!$record->tarefa_grupo_id) {
                            $record->update(['tarefa_grupo_id' => $grupoId]);
                        }

                        $payloadBase = array_merge($data, [
                            'meta_id' => $record->meta_id,
                            'tarefa_grupo_id' => $grupoId,
                            'recorrencia_tipo' => $recorrenciaTipo,
                            'recorrencia_inicio' => $recorrenciaInicio,
                            'recorrencia_dia' => $recorrenciaDia,
                            'recorrencia_repeticoes' => $recorrenciaRepeticoes,
                            'ate_final_projeto' => (bool) $ateFinalProjeto,
                            'datas_personalizadas' => $recorrenciaTipo === 'personalizado' ? $datasPersonalizadas : null,
                        ]);

                        $datas = [];
                        if (RecorrenciaDateGenerator::isFrequenciaPeriodica($recorrenciaTipo)) {
                            $inicio = $recorrenciaInicio ?? now()->toDateString();
                            $dia = (int) $recorrenciaDia;

                            if ($ateFinalProjeto) {
                                $dataEncerramento = $this->getOwnerRecord()->projeto->data_encerramento;
                                if ($dataEncerramento) {
                                    $datas = RecorrenciaDateGenerator::gerarAteData(
                                        $recorrenciaTipo,
                                        $inicio,
                                        $dia,
                                        $dataEncerramento
                                    );
                                }
                            } else {
                                $datas = RecorrenciaDateGenerator::gerar(
                                    $recorrenciaTipo,
                                    $inicio,
                                    $dia,
                                    (int) $recorrenciaRepeticoes
                                );
                            }
                        } elseif ($recorrenciaTipo === 'personalizado' && !empty($datasPersonalizadas)) {
                            $datas = collect($datasPersonalizadas)
                                ->pluck('data')
                                ->filter()
                                ->map(fn ($d) => \Carbon\Carbon::parse($d))
                                ->sort()
                                ->values()
                                ->all();
                        }

                        if (!empty($datas)) {
                            $payloadBase['data_fim'] = $datas[0];
                        }

                        $groupTasks = Tarefa::where('tarefa_grupo_id', $grupoId)->get();
                        if ($groupTasks->isEmpty()) {
                            $groupTasks = collect([$record]);
                        }

                        $targetPoloIds = collect($poloIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
                        $hasTargets = !empty($targetPoloIds);

                        $existingByPolo = $groupTasks->keyBy(fn (Tarefa $t) => $t->polo_id ?? 0);

                        $statusMovimentado = ['em_analise', 'devolvido', 'realizado', 'concluido', 'com_ressalvas'];
                        $hasMovimentadas = function (Tarefa $t) use ($statusMovimentado): bool {
                            if ($t->ocorrencias()->exists()) {
                                return $t->ocorrencias()
                                    ->whereIn('status', $statusMovimentado)
                                    ->exists();
                            }

                            $normalizado = $t->getStatusNormalizado();
                            return $normalizado !== 'pendente';
                        };

                        if ($groupTasks->filter($hasMovimentadas)->count() > 0) {
                            Notification::make()
                                ->title('Algumas ocorrências foram preservadas')
                                ->body('Ocorrências já movimentadas foram mantidas. As pendentes foram recriadas.')
                                ->warning()
                                ->send();
                        }

                        $updateTask = function (Tarefa $task) use ($payloadBase, $responsaveis, $datas, $statusMovimentado): void {
                            $hasOcorrencias = $task->ocorrencias()->exists();
                            if (!$hasOcorrencias && $task->getStatusNormalizado() !== 'pendente') {
                                return;
                            }

                            $task->update($payloadBase);
                            $task->responsaveis()->detach();
                            if (!empty($responsaveis)) {
                                $task->responsaveis()->sync($responsaveis);
                            }

                            if (!$hasOcorrencias) {
                                $task->ocorrencias()->delete();
                                if (!empty($datas)) {
                                    $task->ocorrencias()->createMany(
                                        collect($datas)->map(fn ($d) => ['data_fim' => $d])->all()
                                    );
                                }
                                return;
                            }

                            $movimentadas = $task->ocorrencias()
                                ->whereIn('status', $statusMovimentado)
                                ->get();
                            $datasMovimentadas = $movimentadas
                                ->pluck('data_fim')
                                ->filter()
                                ->map(fn ($d) => $d->format('Y-m-d'))
                                ->all();

                            $task->ocorrencias()
                                ->whereNotIn('status', $statusMovimentado)
                                ->delete();

                            if (!empty($datas)) {
                                $novasDatas = collect($datas)
                                    ->map(fn ($d) => $d->format('Y-m-d'))
                                    ->reject(fn ($d) => in_array($d, $datasMovimentadas, true))
                                    ->unique()
                                    ->values()
                                    ->all();

                                if (!empty($novasDatas)) {
                                    $task->ocorrencias()->createMany(
                                        collect($novasDatas)->map(fn ($d) => ['data_fim' => $d])->all()
                                    );
                                }
                            }
                        };

                        $primeiraAtualizada = null;

                        if (!$hasTargets) {
                            $task = $existingByPolo->get(0) ?? $record;
                            $task->update(array_merge($payloadBase, ['polo_id' => null]));
                            $updateTask($task);
                            $primeiraAtualizada = $task;

                            $groupTasks->filter(fn (Tarefa $t) => $t->id !== $task->id)->each(function (Tarefa $t) use ($hasMovimentadas) {
                                if ($hasMovimentadas($t)) {
                                    return;
                                }
                                $t->responsaveis()->detach();
                                $t->ocorrencias()->delete();
                                $t->delete();
                            });

                            return $primeiraAtualizada;
                        }

                        foreach ($targetPoloIds as $poloId) {
                            $task = $existingByPolo->get($poloId);
                            if ($task) {
                                $task->update(array_merge($payloadBase, ['polo_id' => $poloId]));
                                $updateTask($task);
                            } else {
                                $task = Tarefa::create(array_merge($payloadBase, ['polo_id' => $poloId]));
                                $updateTask($task);
                            }
                            $primeiraAtualizada ??= $task;
                        }

                        $groupTasks->filter(fn (Tarefa $t) => !in_array($t->polo_id, $targetPoloIds, true))
                            ->each(function (Tarefa $t) use ($hasMovimentadas) {
                                if ($hasMovimentadas($t)) {
                                    return;
                                }
                                $t->responsaveis()->detach();
                                $t->ocorrencias()->delete();
                                $t->delete();
                            });

                        return $primeiraAtualizada ?? $record;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
