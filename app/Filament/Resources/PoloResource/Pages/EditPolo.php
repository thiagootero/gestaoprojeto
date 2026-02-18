<?php

namespace App\Filament\Resources\PoloResource\Pages;

use App\Filament\Resources\PoloResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPolo extends EditRecord
{
    protected static string $resource = PoloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
