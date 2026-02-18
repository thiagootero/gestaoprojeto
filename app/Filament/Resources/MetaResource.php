<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MetaResource\Pages;
use App\Filament\Resources\MetaResource\RelationManagers;
use App\Models\Meta;
use App\Models\Projeto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MetaResource extends Resource
{
    protected static ?string $model = Meta::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationGroup = 'Projetos';

    protected static ?string $modelLabel = 'Meta';

    protected static ?string $pluralModelLabel = 'Metas';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Meta')
                    ->schema([
                        Forms\Components\Select::make('projeto_id')
                            ->label('Projeto')
                            ->options(fn () => Projeto::pluck('nome', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Textarea::make('descricao')
                            ->label('Descrição')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('indicador')
                            ->label('Indicador')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('meio_verificacao')
                            ->label('Meio de Verificação')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
                    ->wrap()
                    ->limit(60),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Meta $record): string => $record->status_label)
                    ->color(fn (string $state, Meta $record): string => $record->status_color)
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentual_conclusao')
                    ->label('Progresso')
                    ->suffix('%')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('tarefas_count')
                    ->label('Tarefas')
                    ->counts('tarefas')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('projeto_id')
                    ->label('Projeto')
                    ->relationship('projeto', 'nome')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'em_execucao' => 'Em Execução',
                        'realizado' => 'Realizado',
                    ]),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\TarefasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMetas::route('/'),
            'create' => Pages\CreateMeta::route('/create'),
            'edit' => Pages\EditMeta::route('/{record}/edit'),
        ];
    }
}
