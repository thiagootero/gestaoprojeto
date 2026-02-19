<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjetoResource\Pages;
use App\Filament\Resources\ProjetoResource\RelationManagers;
use App\Filament\Resources\ProjetoResource\Widgets;
use App\Models\Polo;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjetoResource extends Resource
{
    protected static ?string $model = Projeto::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Projetos';

    protected static ?string $modelLabel = 'Projeto';

    protected static ?string $pluralModelLabel = 'Projetos';

    protected static ?int $navigationSort = 1;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Projeto')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Dados Gerais')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\TextInput::make('nome')
                                    ->label('Nome do Projeto')
                                    ->required()
                                    ->maxLength(200)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('descricao')
                                    ->label('Descrição')
                                    ->rows(3)
                                    ->maxLength(2000)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('descricao_breve')
                                    ->label('Descrição breve')
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\DatePicker::make('data_inicio')
                                    ->label('Data de Início')
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->native(),

                                Forms\Components\DatePicker::make('data_encerramento')
                                    ->label('Data de Encerramento')
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->native(),

                                Forms\Components\DatePicker::make('data_limite_prorrogacao_contrato')
                                    ->label('Data limite para prorrogação do contrato')
                                    ->displayFormat('d/m/Y')
                                    ->native(),

                                Forms\Components\DatePicker::make('data_aditivo')
                                    ->label('Data de aditivo')
                                    ->displayFormat('d/m/Y')
                                    ->native(),

                                Forms\Components\Hidden::make('status')
                                    ->default('em_execucao')
                                    ->dehydrated(fn (?Projeto $record): bool => $record === null),

                                Forms\Components\Select::make('polos')
                                    ->label('Polos Vinculados')
                                    ->relationship('polos', 'nome')
                                    ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id'))
                                    ->multiple()
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('financiadores')
                                    ->label('Financiadores')
                                    ->relationship('financiadores', 'nome')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),

                                Forms\Components\Section::make('Anexos')
                                    ->schema([
                                        Forms\Components\Repeater::make('anexos')
                                            ->relationship('anexos')
                                            ->schema([
                                                Forms\Components\Select::make('tipo')
                                                    ->label('Tipo')
                                                    ->options([
                                                        'contrato' => 'Contrato',
                                                        'plano_trabalho' => 'Plano de Trabalho',
                                                        'orcamento' => 'Orçamento',
                                                        'outros' => 'Outros',
                                                    ])
                                                    ->required()
                                                    ->native(false),

                                                Forms\Components\Textarea::make('descricao')
                                                    ->label('Descrição')
                                                    ->required()
                                                    ->rows(2)
                                                    ->columnSpanFull(),

                                                Forms\Components\FileUpload::make('arquivo')
                                                    ->label('Arquivo')
                                                    ->disk('public')
                                                    ->directory('anexos')
                                                    ->preserveFilenames()
                                                    ->openable()
                                                    ->downloadable()
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('+')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible()
                                    ->visible(fn ($livewire): bool => $livewire instanceof \Filament\Resources\Pages\CreateRecord
                                        || $livewire instanceof \Filament\Resources\Pages\EditRecord)
                                    ->columnSpanFull(),

                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'planejamento' => 'Planejamento',
                        'em_execucao' => 'Em Execução',
                        'suspenso' => 'Suspenso',
                        'encerrado' => 'Encerrado',
                        'prestacao_final' => 'Prestação Final',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'planejamento' => 'gray',
                        'em_execucao' => 'success',
                        'suspenso' => 'warning',
                        'encerrado' => 'info',
                        'prestacao_final' => 'primary',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_encerramento')
                    ->label('Encerramento')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('polos.nome')
                    ->label('Polos')
                    ->badge()
                    ->separator(', ')
                    ->searchable(),

                Tables\Columns\TextColumn::make('percentual_conclusao')
                    ->label('Progresso')
                    ->suffix('%')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planejamento' => 'Planejamento',
                        'em_execucao' => 'Em Execução',
                        'suspenso' => 'Suspenso',
                        'encerrado' => 'Encerrado',
                        'prestacao_final' => 'Prestação Final',
                    ]),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AnexosRelationManager::class,
            RelationManagers\EtapasPrestacaoRelationManager::class,
            RelationManagers\MetasRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            Widgets\CalendarioProjetoWidget::class,
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        $user = auth()->user();

        $items = [
            Pages\ViewProjeto::class,
        ];

        // Coordenador financeiro não vê cronograma operacional
        if (!$user?->isCoordenadorFinanceiro()) {
            $items[] = Pages\CronogramaOperacional::class;
        }

        // Apenas Diretor de Projetos, Coordenador Financeiro e Super Admin veem prestação de contas
        if ($user && ($user->isDiretorProjetos() || $user->isCoordenadorFinanceiro() || $user->isSuperAdmin())) {
            $items[] = Pages\CronogramaPrestacaoContas::class;
        }

        $items[] = Pages\CalendarioProjeto::class;

        if ($user && ($user->isAdminGeral() || $user->isDiretorProjetos())) {
            array_splice($items, 1, 0, [
                Pages\EditProjetoPrestacao::class,
                Pages\EditProjetoMetas::class,
            ]);
        }

        return $page->generateNavigationItems($items);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjetos::route('/'),
            'create' => Pages\CreateProjeto::route('/create'),
            'view' => Pages\ViewProjeto::route('/{record}'),
            'edit' => Pages\EditProjeto::route('/{record}/edit'),
            'prestacao' => Pages\EditProjetoPrestacao::route('/{record}/prestacao'),
            'metas' => Pages\EditProjetoMetas::route('/{record}/metas'),
            'cronograma' => Pages\CronogramaProjeto::route('/{record}/cronograma'),
            'cronograma-operacional' => Pages\CronogramaOperacional::route('/{record}/cronograma-operacional'),
            'cronograma-prestacao' => Pages\CronogramaPrestacaoContas::route('/{record}/cronograma-prestacao'),
            'calendario' => Pages\CalendarioProjeto::route('/{record}/calendario'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $user = auth()->user();

        // Coordenador de polo vê apenas projetos dos seus polos + projetos de polos gerais
        if ($user && $user->isCoordenadorPolo()) {
            $poloIds = $user->polos->pluck('id');
            $query->whereHas('polos', function (Builder $q) use ($poloIds) {
                $q->whereIn('polos.id', $poloIds)
                    ->orWhere('polos.is_geral', true);
            });
        }

        return $query;
    }
}
