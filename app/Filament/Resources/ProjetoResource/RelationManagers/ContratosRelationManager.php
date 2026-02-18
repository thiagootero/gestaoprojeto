<?php

namespace App\Filament\Resources\ProjetoResource\RelationManagers;

use App\Models\Financiador;
use App\Models\ProjetoFinanciador;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContratosRelationManager extends RelationManager
{
    protected static string $relationship = 'contratos';

    protected static ?string $title = 'Financiadores';

    protected static ?string $modelLabel = 'Contrato';

    protected static ?string $pluralModelLabel = 'Contratos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('financiador_id')
                    ->label('Financiador')
                    ->options(fn () => Financiador::pluck('nome', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('data_inicio_contrato')
                    ->label('Início do Contrato')
                    ->displayFormat('d/m/Y')
                    ->native(false),

                Forms\Components\DatePicker::make('data_fim_contrato')
                    ->label('Fim do Contrato')
                    ->displayFormat('d/m/Y')
                    ->native(false),

                Forms\Components\DatePicker::make('data_prorrogacao')
                    ->label('Data Limite para Prorrogação')
                    ->displayFormat('d/m/Y')
                    ->native(false),

                Forms\Components\DatePicker::make('prorrogado_ate')
                    ->label('Prorrogado Até')
                    ->displayFormat('d/m/Y')
                    ->native(false),

                Forms\Components\Select::make('periodicidade')
                    ->label('Periodicidade')
                    ->options([
                        '' => 'Manual (sem periodicidade)',
                        'mensal' => 'Mensal',
                        'bimestral' => 'Bimestral',
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual',
                    ])
                    ->native(false)
                    ->helperText('Se definida, permite gerar etapas automaticamente'),

                Forms\Components\TextInput::make('pc_interna_dia')
                    ->label('Dia PC Interna (Qualit.)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->helperText('Dia do mês para prestação qualitativa'),

                Forms\Components\TextInput::make('pc_interna_dia_fin')
                    ->label('Dia PC Interna (Financ.)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(31)
                    ->helperText('Dia do mês para prestação financeira'),

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
            ->recordTitleAttribute('financiador.nome')
            ->columns([
                Tables\Columns\TextColumn::make('financiador.nome')
                    ->label('Financiador')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_inicio_contrato')
                    ->label('Início')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim_contrato')
                    ->label('Fim')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('periodicidade')
                    ->label('Periodicidade')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match($state) {
                        'mensal' => 'Mensal',
                        'bimestral' => 'Bimestral',
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual',
                        default => 'Manual',
                    })
                    ->color(fn (?string $state): string => $state ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('etapas_prestacao_count')
                    ->label('Etapas')
                    ->counts('etapasPrestacao')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('gerarEtapas')
                    ->label('Gerar Etapas')
                    ->icon('heroicon-o-calendar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Gerar Etapas de Prestação')
                    ->modalDescription('Deseja gerar automaticamente as etapas de prestação de contas baseado na periodicidade? Isso criará etapas qualitativas e financeiras.')
                    ->modalSubmitActionLabel('Sim, gerar etapas')
                    ->visible(fn (ProjetoFinanciador $record): bool =>
                        $record->periodicidade !== null &&
                        $record->data_inicio_contrato !== null &&
                        $record->data_fim_contrato !== null
                    )
                    ->action(function (ProjetoFinanciador $record) {
                        $record->gerarEtapas();
                        Notification::make()
                            ->title('Etapas geradas com sucesso!')
                            ->success()
                            ->send();
                    }),
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
