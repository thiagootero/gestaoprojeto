<?php

namespace App\Filament\Resources\PoloResource\Pages;

use App\Filament\Resources\PoloResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPolos extends ListRecords
{
    protected static string $resource = PoloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
