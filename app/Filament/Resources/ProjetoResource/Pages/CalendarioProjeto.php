<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use App\Filament\Resources\ProjetoResource\Widgets\CalendarioProjetoWidget;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class CalendarioProjeto extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ProjetoResource::class;

    protected static string $view = 'filament.resources.projeto-resource.pages.calendario-projeto';

    protected static ?string $title = 'Calendário';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getFooterWidgets(): array
    {
        return [
            CalendarioProjetoWidget::class,
        ];
    }
}
