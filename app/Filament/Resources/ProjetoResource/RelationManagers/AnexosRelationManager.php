<?php

namespace App\Filament\Resources\ProjetoResource\RelationManagers;

use App\Models\Anexo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AnexosRelationManager extends RelationManager
{
    protected static string $relationship = 'anexos';

    protected static ?string $title = 'Anexos';

    protected static ?string $modelLabel = 'Anexo';

    protected static ?string $pluralModelLabel = 'Anexos';

    public function form(Form $form): Form
    {
        return $form
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
                    ->rows(3)
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
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->columns([
                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'contrato' => 'Contrato',
                        'plano_trabalho' => 'Plano de Trabalho',
                        'orcamento' => 'Orçamento',
                        'outros' => 'Outros',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'contrato' => 'primary',
                        'plano_trabalho' => 'info',
                        'orcamento' => 'warning',
                        'outros' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->wrap()
                    ->limit(60)
                    ->searchable(),

                Tables\Columns\TextColumn::make('arquivo')
                    ->label('Arquivo')
                    ->formatStateUsing(fn (?string $state): string => $state ? 'Ver arquivo' : '-')
                    ->url(fn (Anexo $record): ?string => $record->arquivo ? Storage::url($record->arquivo) : null, true)
                    ->openUrlInNewTab()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'contrato' => 'Contrato',
                        'plano_trabalho' => 'Plano de Trabalho',
                        'orcamento' => 'Orçamento',
                        'outros' => 'Outros',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AttachAction::make()
                    ->recordTitleAttribute('descricao'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
