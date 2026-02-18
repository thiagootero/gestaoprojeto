<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoloResource\Pages;
use App\Models\Polo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PoloResource extends Resource
{
    protected static ?string $model = Polo::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Polo';

    protected static ?string $pluralModelLabel = 'Polos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->label('Nome')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Toggle::make('is_geral')
                            ->label('Polo Geral')
                            ->helperText('Polo visível para todos os coordenadores de polo')
                            ->default(false),

                        Forms\Components\Select::make('coordenadores')
                            ->label('Coordenadores')
                            ->relationship('coordenadores', 'name')
                            ->options(fn () => User::where('ativo', true)->whereIn('perfil', ['coordenador_polo', 'diretor_operacoes'])->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Toggle::make('ativo')
                            ->label('Ativo')
                            ->default(true),
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

                Tables\Columns\TextColumn::make('coordenadores.name')
                    ->label('Coordenadores')
                    ->badge()
                    ->separator(', ')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_geral')
                    ->label('Geral')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('ativo')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ativo')
                    ->label('Ativo'),
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
            'index' => Pages\ListPolos::route('/'),
            'create' => Pages\CreatePolo::route('/create'),
            'edit' => Pages\EditPolo::route('/{record}/edit'),
        ];
    }
}
