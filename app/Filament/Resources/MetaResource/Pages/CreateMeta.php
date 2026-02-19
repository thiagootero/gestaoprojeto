<?php

namespace App\Filament\Resources\MetaResource\Pages;

use App\Filament\Resources\MetaResource;
use App\Filament\Resources\ProjetoResource;
use App\Models\Projeto;
use Filament\Resources\Pages\CreateRecord;

class CreateMeta extends CreateRecord
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

    private function resolveProjeto(): ?Projeto
    {
        $projetoId = request()->query('projeto_id');
        if (!$projetoId) {
            return null;
        }

        return Projeto::find($projetoId);
    }
}
