<?php

namespace App\Filament\Resources\ProjetoResource\RelationManagers;

use App\Models\Meta;
use App\Models\Polo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MetasRelationManager extends RelationManager
{
    protected static string $relationship = 'metas';

    protected static ?string $title = 'Metas';

    protected static ?string $modelLabel = 'Meta';

    protected static ?string $pluralModelLabel = 'Metas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->limit(80),

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
            ->defaultSort('numero', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'em_execucao' => 'Em Execução',
                        'realizado' => 'Realizado',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('tarefas')
                    ->label('Tarefas')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn (Meta $record): string => route('filament.admin.resources.metas.edit', ['record' => $record])),
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
