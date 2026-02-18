<?php

namespace App\Filament\Widgets;

use App\Models\EtapaPrestacao;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProximasPrestacoesInternas extends BaseWidget
{
    protected static ?string $heading = 'Próximas Prestações (Internas)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EtapaPrestacao::query()
                    ->with(['projeto'])
                    ->where('origem', 'interna')
                    ->whereIn('status', ['pendente', 'em_execucao', 'em_elaboracao'])
                    ->orderBy('data_limite', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('projeto.nome')
                    ->label('Projeto')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Descrição')
                    ->searchable()
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

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Dias')
                    ->alignCenter()
                    ->badge()
                    ->formatStateUsing(function (int $state): string {
                        if ($state < 0) {
                            return abs($state) . ' atrasado';
                        }
                        return $state . ' dias';
                    })
                    ->color(function (EtapaPrestacao $record): string {
                        $dias = $record->dias_restantes;
                        if ($dias < 0) return 'danger';
                        if ($dias <= 15) return 'danger';
                        if ($dias <= 30) return 'warning';
                        return 'success';
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, EtapaPrestacao $record): string => $record->status_label)
                    ->color(fn (string $state, EtapaPrestacao $record): string => $record->status_color),
            ])
            ->paginated(false);
    }
}
