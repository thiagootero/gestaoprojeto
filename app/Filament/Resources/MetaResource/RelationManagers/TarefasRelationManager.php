<?php

namespace App\Filament\Resources\MetaResource\RelationManagers;

use App\Models\Polo;
use App\Models\Tarefa;
use App\Models\User;
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
                    ->helperText('Selecione um ou mais polos. Deixe vazio para tarefa administrativa'),

                Forms\Components\Select::make('responsaveis')
                    ->label('Responsáveis')
                    ->relationship('responsaveis', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                Forms\Components\Select::make('recorrencia_tipo')
                    ->label('Recorrência')
                    ->options([
                        'nenhuma' => 'Sem recorrência',
                        'dia_mes' => 'Todo dia X',
                    ])
                    ->native(false)
                    ->default('nenhuma')
                    ->reactive(),

                Forms\Components\DatePicker::make('recorrencia_inicio')
                    ->label('Iniciar em')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get): bool => $get('recorrencia_tipo') !== 'nenhuma')
                    ->visible(fn (Get $get): bool => $get('recorrencia_tipo') !== 'nenhuma'),

                Forms\Components\TextInput::make('recorrencia_dia')
                    ->label('Dia do mês')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->required(fn (Get $get): bool => $get('recorrencia_tipo') === 'dia_mes')
                    ->visible(fn (Get $get): bool => $get('recorrencia_tipo') === 'dia_mes'),

                Forms\Components\TextInput::make('recorrencia_repeticoes')
                    ->label('Quantidade de repetições')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(120)
                    ->required(fn (Get $get): bool => $get('recorrencia_tipo') !== 'nenhuma')
                    ->visible(fn (Get $get): bool => $get('recorrencia_tipo') !== 'nenhuma'),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data Fim/Prazo')
                    ->displayFormat('d/m/Y')
                    ->native()
                    ->required(fn (Get $get): bool => ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma')
                    ->visible(fn (Get $get): bool => ($get('recorrencia_tipo') ?? 'nenhuma') === 'nenhuma'),

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
                        unset($data['polo_ids']);
                        unset($data['responsaveis']);
                        unset(
                            $data['recorrencia_tipo'],
                            $data['recorrencia_dia'],
                            $data['recorrencia_inicio'],
                            $data['recorrencia_repeticoes']
                        );
                        $data['meta_id'] = $this->getOwnerRecord()->id;

                        $datas = [];
                        if ($recorrenciaTipo !== 'nenhuma') {
                            $inicio = \Carbon\Carbon::parse($recorrenciaInicio ?? now())->startOfDay();
                            $cursor = $inicio->copy();
                            $repeticoes = max(1, (int) $recorrenciaRepeticoes);

                            if ($recorrenciaTipo === 'dia_mes') {
                                $dia = (int) $recorrenciaDia;
                                if ($dia >= 1 && $dia <= 31) {
                                    $cursor = $cursor->copy()->day(min($dia, $cursor->daysInMonth));
                                    if ($cursor->lt($inicio)) {
                                        $cursor = $cursor->addMonthNoOverflow()->day(min($dia, $cursor->daysInMonth));
                                    }
                                    while (count($datas) < $repeticoes) {
                                        $datas[] = $cursor->copy();
                                        $cursor = $cursor->addMonthNoOverflow()->day(min($dia, $cursor->daysInMonth));
                                    }
                                }
                            }
                        }

                        if (empty($poloIds)) {
                            if (!empty($datas)) {
                                $payload = $data;
                                $payload['data_fim'] = $datas[0];
                                $created = Tarefa::create($payload);
                                $created->ocorrencias()->createMany(
                                    collect($datas)->map(fn ($d) => ['data_fim' => $d])->all()
                                );
                                if (!empty($responsaveis)) {
                                    $created->responsaveis()->sync($responsaveis);
                                }
                                return $created;
                            }

                            $created = Tarefa::create($data);
                            if (!empty($responsaveis)) {
                                $created->responsaveis()->sync($responsaveis);
                            }
                            return $created;
                        }

                        $primeira = null;
                        foreach ($poloIds as $poloId) {
                            if (!empty($datas)) {
                                $payload = $data;
                                $payload['polo_id'] = $poloId;
                                $payload['data_fim'] = $datas[0];
                                $created = Tarefa::create($payload);
                                $created->ocorrencias()->createMany(
                                    collect($datas)->map(fn ($d) => ['data_fim' => $d])->all()
                                );
                                if (!empty($responsaveis)) {
                                    $created->responsaveis()->sync($responsaveis);
                                }
                                $primeira ??= $created;
                                continue;
                            }
                            $payload = $data;
                            $payload['polo_id'] = $poloId;
                            $created = Tarefa::create($payload);
                            if (!empty($responsaveis)) {
                                $created->responsaveis()->sync($responsaveis);
                            }
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
