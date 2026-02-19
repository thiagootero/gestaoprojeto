<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TarefaResource\Pages;
use App\Models\Meta;
use App\Models\Polo;
use App\Models\Projeto;
use App\Models\Tarefa;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TarefaResource extends Resource
{
    protected static ?string $model = Tarefa::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Projetos';

    protected static ?string $modelLabel = 'Tarefa';

    protected static ?string $pluralModelLabel = 'Tarefas';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Tarefa')
                    ->schema([
                        Forms\Components\Select::make('meta_id')
                            ->label('Meta')
                            ->options(function () {
                                return Meta::with('projeto')
                                    ->get()
                                    ->mapWithKeys(fn ($meta) => [
                                        $meta->id => "{$meta->projeto->nome} - Meta {$meta->numero}"
                                    ]);
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('numero')
                            ->label('Número')
                            ->required()
                            ->maxLength(10),

                        Forms\Components\Select::make('polo_id')
                            ->label('Polo')
                            ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pendente' => 'Pendente',
                                'em_execucao' => 'Em Execução',
                                'realizado' => 'Realizado',
                            ])
                            ->required()
                            ->native(false)
                            ->afterStateHydrated(function ($state, callable $set, ?Tarefa $record) {
                                if ($record) {
                                    $set('status', $record->getStatusNormalizado());
                                }
                            }),

                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('responsavel')
                            ->label('Responsável (texto)'),

                        Forms\Components\Select::make('responsavel_user_id')
                            ->label('Responsável (usuário)')
                            ->options(fn () => User::where('ativo', true)->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('data_inicio')
                            ->label('Data de Início')
                            ->displayFormat('d/m/Y')
                            ->native(false),

                        Forms\Components\DatePicker::make('data_fim')
                            ->label('Data Fim/Prazo')
                            ->displayFormat('d/m/Y')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ocorrências')
                    ->schema([
                        Forms\Components\Repeater::make('ocorrencias')
                            ->relationship('ocorrencias')
                            ->schema([
                                Forms\Components\DatePicker::make('data_fim')
                                    ->label('Data')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->required(),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar data')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (?Tarefa $record): bool => $record !== null && $record->ocorrencias()->exists())
                    ->collapsible(),

                Forms\Components\Section::make('Comprovação')
                    ->schema([
                        Forms\Components\TextInput::make('link_comprovacao')
                            ->label('Link de Comprovação')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('comprovacao_validada')
                            ->label('Comprovação Validada'),

                        Forms\Components\Textarea::make('como_fazer')
                            ->label('Como Fazer')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('meta.projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->sortable()
                    ->searchable(),

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

                Tables\Columns\IconColumn::make('comprovacao_validada')
                    ->label('Validada')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('projeto')
                    ->label('Projeto')
                    ->options(fn () => Projeto::pluck('nome', 'id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->whereHas('meta', fn ($q) => $q->where('projeto_id', $value))
                        );
                    }),
                Tables\Filters\SelectFilter::make('polo_id')
                    ->label('Polo')
                    ->relationship('polo', 'nome'),
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
                Tables\Filters\Filter::make('atrasadas')
                    ->label('Apenas Atrasadas')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereNotNull('data_fim')
                        ->where('data_fim', '<', now())
                        ->whereNotIn('status', ['realizado', 'concluido', 'com_ressalvas'])
                    ),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTarefas::route('/'),
            'create' => Pages\CreateTarefa::route('/create'),
            'edit' => Pages\EditTarefa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Filtrar por polo se for coordenador de polo
        $user = Auth::user();
        if ($user && $user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');
            $geralIds = \App\Models\Polo::where('is_geral', true)->pluck('id');
            $allPoloIds = $poloIds->merge($geralIds)->unique();
            $query->whereIn('polo_id', $allPoloIds);
        }

        return $query;
    }
}
