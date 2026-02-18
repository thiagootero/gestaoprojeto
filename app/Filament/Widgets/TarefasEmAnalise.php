<?php

namespace App\Filament\Widgets;

use App\Models\Tarefa;
use App\Notifications\TarefaAprovada;
use App\Notifications\TarefaDevolvida;
use App\Support\NotificacaoCentral;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TarefasEmAnalise extends BaseWidget
{
    protected static ?string $heading = 'Tarefas Pendentes de Análise';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isAdminGeral()
            || $user?->isDiretorProjetos();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tarefa::query()
                    ->with(['meta.projeto', 'polo', 'realizacoes.user'])
                    ->where('status', 'em_analise')
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('meta.projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('polo.nome')
                    ->label('Polo')
                    ->badge()
                    ->placeholder('Geral'),

                Tables\Columns\TextColumn::make('responsavel')
                    ->label('Responsável')
                    ->limit(25),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('analisar')
                    ->label('Analisar')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->modalHeading('Análise da Tarefa')
                    ->modalSubmitActionLabel('Confirmar')
                    ->modalWidth('lg')
                    ->form(function (Tarefa $record): array {
                        $realizacao = $record->realizacoes->last();
                        $enviadoPor = $realizacao?->user?->name ?? 'N/A';
                        $enviadoEm = $realizacao?->created_at?->format('d/m/Y H:i') ?? '';
                        $comentario = $realizacao?->comentario ?? 'Nenhum comentário.';

                        return [
                            Forms\Components\Placeholder::make('info_envio')
                                ->label('Enviado por')
                                ->content($enviadoPor . ($enviadoEm ? ' em ' . $enviadoEm : '')),

                            Forms\Components\Placeholder::make('comentario_envio')
                                ->label('Comentário enviado')
                                ->content($comentario),

                            Forms\Components\Select::make('decisao')
                                ->label('Decisão')
                                ->options([
                                    'aprovar' => 'Aprovar',
                                    'devolver' => 'Devolver para ajuste',
                                ])
                                ->required()
                                ->native(false)
                                ->reactive(),

                            Forms\Components\Textarea::make('observacao')
                                ->label('Observação')
                                ->rows(3)
                                ->maxLength(2000)
                                ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'aprovar'),

                            Forms\Components\Textarea::make('motivo')
                                ->label('Motivo da devolução')
                                ->rows(3)
                                ->maxLength(2000)
                                ->required(fn (Forms\Get $get): bool => $get('decisao') === 'devolver')
                                ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'devolver'),
                        ];
                    })
                    ->action(function (Tarefa $record, array $data) {
                        $user = auth()->user();

                        if ($data['decisao'] === 'aprovar') {
                            $record->update([
                                'status' => 'realizado',
                                'comprovacao_validada' => true,
                                'validado_por' => $user->id,
                                'validado_em' => now(),
                            ]);

                            if (!empty($data['observacao'])) {
                                $obs = $record->observacoes;
                                $record->update([
                                    'observacoes' => ($obs ? $obs . "\n" : '') . '[Validado por ' . $user->name . '] ' . $data['observacao'],
                                ]);
                            }

                            $enviadoPor = NotificacaoCentral::ultimoEnvioTarefa($record);
                            if ($enviadoPor && $enviadoPor->id !== $user->id) {
                                $enviadoPor->notify(new TarefaAprovada($record, $user));
                            }
                        } else {
                            $obs = $record->observacoes;
                            $record->update([
                                'status' => 'devolvido',
                                'comprovacao_validada' => false,
                                'validado_por' => null,
                                'validado_em' => null,
                                'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                            ]);

                            $enviadoPor = NotificacaoCentral::ultimoEnvioTarefa($record);
                            if ($enviadoPor && $enviadoPor->id !== $user->id) {
                                $enviadoPor->notify(new TarefaDevolvida($record, $user, $data['motivo']));
                            }
                        }
                    }),
            ])
            ->paginated(false);
    }
}
