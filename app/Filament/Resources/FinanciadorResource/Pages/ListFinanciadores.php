<?php

namespace App\Filament\Resources\FinanciadorResource\Pages;

use App\Filament\Resources\FinanciadorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinanciadores extends ListRecords
{
    protected static string $resource = FinanciadorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
