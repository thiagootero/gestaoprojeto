<?php

namespace App\Filament\Resources\ProjetoResource\RelationManagers;

use App\Models\EtapaPrestacao;
use App\Support\RecorrenciaDateGenerator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EtapasPrestacaoRelationManager extends RelationManager
{
    protected static string $relationship = 'etapasPrestacao';

    protected static ?string $title = 'Prestações de Contas';

    protected static ?string $modelLabel = 'Etapa de Prestação';

    protected static ?string $pluralModelLabel = 'Etapas de Prestação';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['projeto_id'] = $this->getOwnerRecord()->id;
        if (($data['origem'] ?? 'financiador') === 'interna') {
            $data['projeto_financiador_id'] = null;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['projeto_id'] = $this->getOwnerRecord()->id;
        if (($data['origem'] ?? 'financiador') === 'interna') {
            $data['projeto_financiador_id'] = null;
        }

        unset(
            $data['recorrencia_tipo'],
            $data['recorrencia_dia'],
            $data['recorrencia_inicio'],
            $data['recorrencia_repeticoes'],
            $data['ate_final_projeto'],
            $data['datas_personalizadas']
        );

        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('origem')
                    ->label('Origem')
                    ->options([
                        'financiador' => 'Financiador',
                        'interna' => 'Tarefa interna',
                    ])
                    ->required()
                    ->native(false)
                    ->default('financiador')
                    ->reactive(),

                Forms\Components\Select::make('projeto_financiador_id')
                    ->label('Contrato/Financiador')
                    ->options(function () {
                        return $this->getOwnerRecord()
                            ->contratos()
                            ->with('financiador')
                            ->get()
                            ->mapWithKeys(fn ($contrato) => [
                                $contrato->id => $contrato->financiador->nome
                            ]);
                    })
                    ->searchable()
                    ->required(fn (Get $get): bool => $get('origem') === 'financiador')
                    ->disabled(fn (Get $get): bool => $get('origem') === 'interna')
                    ->visible(fn (Get $get): bool => $get('origem') === 'financiador'),

                Forms\Components\TextInput::make('descricao')
                    ->label('Descrição')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\Select::make('tipo')
                    ->label('Tipo')
                    ->options(function (?EtapaPrestacao $record): array {
                        $options = [
                            'qualitativa' => 'Qualitativa',
                            'financeira' => 'Financeira',
                        ];

                        if (!$record) {
                            $options['ambas'] = 'Qualitativa + Financeira';
                        }

                        return $options;
                    })
                    ->required()
                    ->native(false),

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

                // --- Data limite (criação sem recorrência OU sempre na edição) ---
                Forms\Components\DatePicker::make('data_limite')
                    ->label('Data Limite')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->visible(fn (Get $get, string $operation): bool =>
                        $operation === 'edit'
                        || ($operation === 'create' && ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                    ),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_etapa')
            ->columns([
                Tables\Columns\TextColumn::make('origem')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'interna' ? 'Interna' : 'Financiador')
                    ->color(fn (string $state): string => $state === 'interna' ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('projetoFinanciador.financiador.nome')
                    ->label('Financiador')
                    ->placeholder('Interna')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('numero_etapa')
                    ->label('Etapa')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'qualitativa' => 'Qualitativa',
                        'financeira' => 'Financeira',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'qualitativa' => 'info',
                        'financeira' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_limite')
                    ->label('Data Limite')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (EtapaPrestacao $record): string => $record->urgencia_color),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, EtapaPrestacao $record): string => $record->status_label)
                    ->color(fn (string $state, EtapaPrestacao $record): string => $record->status_color)
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_envio')
                    ->label('Enviada em')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Dias')
                    ->alignCenter()
                    ->formatStateUsing(fn (int $state): string => $state < 0 ? "{$state} (atrasado)" : $state)
                    ->color(fn (EtapaPrestacao $record): string => $record->urgencia_color),
            ])
            ->defaultSort('data_limite', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'qualitativa' => 'Qualitativa',
                        'financeira' => 'Financeira',
                    ]),
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
                Tables\Filters\SelectFilter::make('projeto_financiador_id')
                    ->label('Financiador')
                    ->options(function () {
                        return $this->getOwnerRecord()
                            ->contratos()
                            ->with('financiador')
                            ->get()
                            ->mapWithKeys(fn ($contrato) => [
                                $contrato->id => $contrato->financiador->nome
                            ]);
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->using(function (array $data) {
                        $recorrenciaTipo = $data['recorrencia_tipo'] ?? 'nenhuma';
                        $recorrenciaDia = $data['recorrencia_dia'] ?? null;
                        $recorrenciaInicio = $data['recorrencia_inicio'] ?? null;
                        $recorrenciaRepeticoes = $data['recorrencia_repeticoes'] ?? null;
                        $ateFinalProjeto = $data['ate_final_projeto'] ?? false;
                        $datasPersonalizadas = $data['datas_personalizadas'] ?? [];

                        unset(
                            $data['recorrencia_tipo'],
                            $data['recorrencia_dia'],
                            $data['recorrencia_inicio'],
                            $data['recorrencia_repeticoes'],
                            $data['ate_final_projeto'],
                            $data['datas_personalizadas']
                        );

                        $data['projeto_id'] = $this->getOwnerRecord()->id;
                        if (($data['origem'] ?? 'financiador') === 'interna') {
                            $data['projeto_financiador_id'] = null;
                        }

                        $datas = [];
                        if (RecorrenciaDateGenerator::isFrequenciaPeriodica($recorrenciaTipo)) {
                            $inicio = $recorrenciaInicio ?? now()->toDateString();
                            $dia = (int) $recorrenciaDia;

                            if ($ateFinalProjeto) {
                                $dataEncerramento = $this->getOwnerRecord()->data_encerramento;
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

                        $createEtapa = function (array $payload): EtapaPrestacao {
                            if ($payload['tipo'] !== 'ambas') {
                                return EtapaPrestacao::create($payload);
                            }

                            $qualitativa = $payload;
                            $qualitativa['tipo'] = 'qualitativa';
                            $financeira = $payload;
                            $financeira['tipo'] = 'financeira';

                            $created = EtapaPrestacao::create($qualitativa);
                            EtapaPrestacao::create($financeira);
                            return $created;
                        };

                        if (!empty($datas)) {
                            $primeira = null;
                            foreach ($datas as $dataLimite) {
                                $payload = $data;
                                $payload['data_limite'] = $dataLimite;
                                $created = $createEtapa($payload);
                                $primeira ??= $created;
                            }
                            return $primeira;
                        }

                        return $createEtapa($data);
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
