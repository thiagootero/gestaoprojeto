<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjetos extends ListRecords
{
    protected static string $resource = ProjetoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
