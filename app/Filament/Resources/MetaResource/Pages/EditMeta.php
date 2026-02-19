<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use App\Filament\Resources\ProjetoResource;
use App\Models\Projeto;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeta extends EditRecord
{
    protected static string $resource = MetaResource::class;

    public function getBreadcrumbs(): array
    {
        $projeto = $this->resolveProjeto();
        if ($projeto) {
            return [
                ProjetoResource::getUrl('index') => 'Projetos',
                ProjetoResource::getUrl('view', ['record' => $projeto]) => $projeto->nome,
                static::getTitle(),
            ];
        }

        return [
            ProjetoResource::getUrl('index') => 'Projetos',
            static::getTitle(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    private function resolveProjeto(): ?Projeto
    {
        if ($this->record?->projeto) {
            return $this->record->projeto;
        }

        $projetoId = request()->query('projeto_id');
        if (!$projetoId) {
            return null;
        }

        return Projeto::find($projetoId);
    }
}
