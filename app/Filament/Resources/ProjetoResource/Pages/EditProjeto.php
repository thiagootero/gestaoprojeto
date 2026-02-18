<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjeto extends EditRecord
{
    protected static string $resource = ProjetoResource::class;

    protected static ?string $navigationLabel = 'Editar Projeto';

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdminGeral() || $user->isDiretorProjeto() || in_array($user->perfil, ['super_admin', 'diretor_projetos']));
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

    public function getRelationManagers(): array
    {
        return [];
    }
}
