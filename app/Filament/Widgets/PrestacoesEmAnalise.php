<?php

namespace App\Filament\Widgets;

use App\Models\EtapaPrestacao;
use App\Notifications\PrestacaoAprovada;
use App\Notifications\PrestacaoDevolvida;
use App\Support\NotificacaoCentral;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PrestacoesEmAnalise extends BaseWidget
{
    protected static ?string $heading = 'Prestações Pendentes de Validação';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user?->isAdminGeral()
            || $user?->isDiretorOperacoes()
            || $user?->isDiretorProjetos();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EtapaPrestacao::query()
                    ->with(['projeto', 'projetoFinanciador.financiador', 'realizacoes.user'])
                    ->where('status', 'em_analise')
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('projetoFinanciador.financiador.nome')
                    ->label('Financiador')
                    ->badge()
                    ->placeholder('Interna'),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->limit(40)
                    ->wrap(),

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
                    }),

                Tables\Columns\TextColumn::make('data_limite')
                    ->label('Data Limite')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('realizacoes.user.name')
                    ->label('Enviado por')
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
                    ->modalHeading('Análise da Prestação de Contas')
                    ->modalSubmitActionLabel('Confirmar')
                    ->modalWidth('lg')
                    ->form(function (EtapaPrestacao $record): array {
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
                                    'rejeitar' => 'Devolver para ajuste',
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
                                ->required(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar')
                                ->visible(fn (Forms\Get $get): bool => $get('decisao') === 'rejeitar'),
                        ];
                    })
                    ->action(function (EtapaPrestacao $record, array $data) {
                        $user = auth()->user();

                        if ($data['decisao'] === 'aprovar') {
                            $updateData = [
                                'status' => 'realizado',
                                'validado_por' => $user->id,
                                'validado_em' => now(),
                            ];

                            if (!empty($data['observacao'])) {
                                $obs = $record->observacoes;
                                $updateData['observacoes'] = ($obs ? $obs . "\n" : '') . '[Validado por ' . $user->name . '] ' . $data['observacao'];
                            }

                            $record->update($updateData);

                            $enviadoPor = NotificacaoCentral::ultimoEnvioPrestacao($record);
                            if ($enviadoPor && $enviadoPor->id !== $user->id) {
                                $enviadoPor->notify(new PrestacaoAprovada($record, $user));
                            }
                        } else {
                            $obs = $record->observacoes;
                            $record->update([
                                'status' => 'devolvido',
                                'validado_por' => null,
                                'validado_em' => null,
                                'observacoes' => ($obs ? $obs . "\n" : '') . '[Devolvido por ' . $user->name . '] ' . $data['motivo'],
                            ]);

                            $enviadoPor = NotificacaoCentral::ultimoEnvioPrestacao($record);
                            if ($enviadoPor && $enviadoPor->id !== $user->id) {
                                $enviadoPor->notify(new PrestacaoDevolvida($record, $user, $data['motivo']));
                            }
                        }
                    }),
            ])
            ->paginated(false);
    }
}
