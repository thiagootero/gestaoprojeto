<?php

namespace App\Filament\Resources\MetaResource\RelationManagers;

use App\Models\Polo;
use App\Models\Tarefa;
use App\Models\User;
use App\Support\RecorrenciaDateGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
                    ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id'))
                    ->multiple()
                    ->native()
                    ->dehydrated(false)
                    ->helperText('Selecione um ou mais polos. Deixe vazio para tarefa administrativa')
                    ->visible(fn (string $operation): bool => $operation === 'create'),

                Forms\Components\Select::make('responsaveis')
                    ->label('Responsáveis')
                    ->relationship('responsaveis', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

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
                    ->visible(fn (string $operation): bool => $operation === 'create'),

                Forms\Components\DatePicker::make('recorrencia_inicio')
                    ->label('Iniciar em')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma'))
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\TextInput::make('recorrencia_dia')
                    ->label('Dia do mês')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma'))
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\Toggle::make('ate_final_projeto')
                    ->label('Até o final do projeto')
                    ->default(false)
                    ->reactive()
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma')),

                Forms\Components\TextInput::make('recorrencia_repeticoes')
                    ->label('Quantidade de repetições')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->required(fn (Get $get): bool => RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma') && !$get('ate_final_projeto'))
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && RecorrenciaDateGenerator::isFrequenciaPeriodica($get('recorrencia_tipo') ?? 'nenhuma') && !$get('ate_final_projeto')),

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
                    ->visible(fn (Get $get, string $operation): bool => $operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'personalizado')
                    ->columnSpanFull(),

                // --- Data única (criação sem recorrência OU edição sem ocorrências) ---
                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data Fim/Prazo')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get, string $operation, ?Tarefa $record): bool =>
                        ($operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                        || ($operation === 'edit' && $record && !$record->ocorrencias()->exists())
                    )
                    ->visible(fn (Get $get, string $operation, ?Tarefa $record): bool =>
                        ($operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                        || ($operation === 'edit' && $record && !$record->ocorrencias()->exists())
                    ),

                // --- Ocorrências (edição de tarefa que já possui ocorrências) ---
                Forms\Components\Repeater::make('ocorrencias')
                    ->label('Datas das Ocorrências')
                    ->relationship('ocorrencias')
                    ->schema([
                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data')
                            ->displayFormat('d/m/Y')
                            ->native()
                            ->required(),
                    ])
                    ->columns(1)
                    ->defaultItems(0)
                    ->addActionLabel('Adicionar data')
                    ->visible(fn (string $operation, ?Tarefa $record): bool =>
                        $operation === 'edit' && $record !== null && $record->ocorrencias()->exists()
                    )
                    ->columnSpanFull(),

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
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('polo.nome')
                    ->label('Polo')
                    ->badge()
                    ->placeholder('NSA')
                    ->sortable(),

                Tables\Columns\TextColumn::make('responsavel')
                    ->label('Responsável')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Prazo')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Tarefa $record): string =>
                        $record->atrasada ? 'danger' : ($record->vencendo ? 'warning' : 'gray')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Tarefa $record): string => $record->status_label)
                    ->color(fn (string $state, Tarefa $record): string => $record->status_color)
                    ->sortable(),

                Tables\Columns\IconColumn::make('link_comprovacao')
                    ->label('Comprov.')
                    ->boolean()
                    ->trueIcon('heroicon-o-link')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),

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
                        $data['meta_id'] = $this->getOwnerRecord()->id;

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
