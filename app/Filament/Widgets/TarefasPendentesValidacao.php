<?php

namespace App\Filament\Widgets;

use App\Models\Tarefa;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TarefasPendentesValidacao extends BaseWidget
{
    protected static ?string $heading = 'Tarefas Pendentes de Validação';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tarefa::query()
                    ->with(['meta.projeto', 'polo'])
                    ->whereIn('status', ['realizado', 'concluido'])
                    ->where('comprovacao_validada', false)
                    ->whereNotNull('link_comprovacao')
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('meta.projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('numero')
                    ->label('Nº')
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('polo.nome')
                    ->label('Polo')
                    ->badge()
                    ->placeholder('NSA'),

                Tables\Columns\TextColumn::make('responsavel')
                    ->label('Responsável')
                    ->limit(25),

                Tables\Columns\TextColumn::make('link_comprovacao')
                    ->label('Comprovação')
                    ->icon('heroicon-o-link')
                    ->url(fn (Tarefa $record): ?string => $record->link_comprovacao)
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn (): string => 'Ver'),
            ])
            ->actions([
                Tables\Actions\Action::make('validar')
                    ->label('Validar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Validar Comprovação')
                    ->modalDescription('Você confirma que verificou a comprovação e está tudo correto?')
                    ->action(function (Tarefa $record) {
                        $record->update([
                            'comprovacao_validada' => true,
                            'validado_por' => auth()->id(),
                            'validado_em' => now(),
                        ]);
                    }),
            ])
            ->paginated(false);
    }
}
