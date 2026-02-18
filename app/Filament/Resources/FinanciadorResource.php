<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinanciadorResource\Pages;
use App\Models\Financiador;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FinanciadorResource extends Resource
{
    protected static ?string $model = Financiador::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Financiador';

    protected static ?string $pluralModelLabel = 'Financiadores';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(200)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('tipo')
                            ->label('Tipo')
                            ->options([
                                'publico' => 'Público',
                                'privado' => 'Privado',
                                'internacional' => 'Internacional',
                                'misto' => 'Misto',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('contato_nome')
                            ->label('Nome do Contato')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('contato_email')
                            ->label('E-mail do Contato')
                            ->email()
                            ->maxLength(100),

                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'publico' => 'Público',
                        'privado' => 'Privado',
                        'internacional' => 'Internacional',
                        'misto' => 'Misto',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'publico' => 'info',
                        'privado' => 'success',
                        'internacional' => 'warning',
                        'misto' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('contato_nome')
                    ->label('Contato')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('contato_email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'publico' => 'Público',
                        'privado' => 'Privado',
                        'internacional' => 'Internacional',
                        'misto' => 'Misto',
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinanciadores::route('/'),
            'create' => Pages\CreateFinanciador::route('/create'),
            'edit' => Pages\EditFinanciador::route('/{record}/edit'),
        ];
    }
}
