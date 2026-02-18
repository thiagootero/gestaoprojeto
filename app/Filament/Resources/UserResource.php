<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Polo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->maxLength(255),

                        Forms\Components\Select::make('perfil')
                            ->label('Perfil')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'admin_geral' => 'Administrador Geral',
                                'diretor_projetos' => 'Diretor de Projetos',
                                'diretor_operacoes' => 'Diretor de Operações',
                                'coordenador_polo' => 'Coordenador de Polo',
                                'coordenador_financeiro' => 'Coordenador Financeiro',
                            ])
                            ->required()
                            ->native(false)
                            ->live(),

                        Forms\Components\Select::make('polos')
                            ->label('Polos')
                            ->relationship('polos', 'nome')
                            ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get): bool => in_array($get('perfil'), ['coordenador_polo', 'diretor_operacoes'])),

                        Forms\Components\TextInput::make('cargo')
                            ->label('Cargo')
                            ->maxLength(100),

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
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('perfil_label')
                    ->label('Perfil')
                    ->badge()
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('perfil', $direction)),

                Tables\Columns\TextColumn::make('polos.nome')
                    ->label('Polos')
                    ->badge()
                    ->separator(', ')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('cargo')
                    ->label('Cargo')
                    ->searchable()
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('perfil')
                    ->label('Perfil')
                    ->options([
                        'super_admin' => 'Super Admin',
                        'admin_geral' => 'Administrador Geral',
                        'diretor_projetos' => 'Diretor de Projetos',
                        'diretor_operacoes' => 'Diretor de Operações',
                        'coordenador_polo' => 'Coordenador de Polo',
                        'coordenador_financeiro' => 'Coordenador Financeiro',
                    ]),
                Tables\Filters\SelectFilter::make('polos')
                    ->label('Polo')
                    ->relationship('polos', 'nome'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
