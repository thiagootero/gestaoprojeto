<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use App\Filament\Resources\ProjetoResource\RelationManagers\MetasRelationManager;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjetoMetas extends EditRecord
{
    protected static string $resource = ProjetoResource::class;

    protected static ?string $navigationLabel = 'Metas/Tarefas';

    protected static ?string $title = 'Metas/Tarefas';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdminGeral() || $user->isDiretorProjeto() || in_array($user->perfil, ['super_admin', 'diretor_projetos']));
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function getRelationManagers(): array
    {
        return [
            MetasRelationManager::class,
        ];
    }
}
