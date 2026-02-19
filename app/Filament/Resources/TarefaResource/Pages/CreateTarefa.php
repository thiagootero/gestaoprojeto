<?php

namespace App\Filament\Resources\TarefaResource\Pages;

use App\Filament\Resources\TarefaResource;
use App\Filament\Resources\ProjetoResource;
use App\Models\Meta;
use App\Models\Projeto;
use Filament\Resources\Pages\CreateRecord;

class CreateTarefa extends CreateRecord
{
    protected static string $resource = TarefaResource::class;

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
        $metaId = request()->query('meta_id');
        if (!$metaId) {
            return null;
        }

        $meta = Meta::with('projeto')->find($metaId);
        return $meta?->projeto;
    }
}
