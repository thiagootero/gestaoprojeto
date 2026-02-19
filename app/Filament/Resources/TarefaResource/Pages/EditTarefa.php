<?php

namespace App\Filament\Resources\TarefaResource\Pages;

use App\Filament\Resources\TarefaResource;
use App\Filament\Resources\ProjetoResource;
use App\Models\Meta;
use App\Models\Projeto;
use App\Support\RecorrenciaDateGenerator;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;

class EditTarefa extends EditRecord
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('adicionar_ocorrencias')
                ->label('Adicionar Ocorrências')
                ->icon('heroicon-o-calendar-days')
                ->color('info')
                ->visible(fn () => !$this->record->ocorrencias()->exists())
                ->form([
                    Forms\Components\Select::make('modo')
                        ->label('Modo')
                        ->options([
                            'periodico' => 'Periódico',
                            'personalizado' => 'Datas personalizadas',
                        ])
                        ->required()
                        ->native(false)
                        ->default('periodico')
                        ->reactive(),

                    Forms\Components\Select::make('frequencia')
                        ->label('Frequência')
                        ->options([
                            'mensal' => 'Mensal',
                            'bimestral' => 'Bimestral',
                            'trimestral' => 'Trimestral',
                            'semestral' => 'Semestral',
                            'anual' => 'Anual',
                        ])
                        ->required(fn (Get $get): bool => $get('modo') === 'periodico')
                        ->visible(fn (Get $get): bool => $get('modo') === 'periodico')
                        ->native(false),

                    Forms\Components\DatePicker::make('inicio')
                        ->label('Iniciar em')
                        ->displayFormat('d/m/Y')
                        ->native()
                        ->required(fn (Get $get): bool => $get('modo') === 'periodico')
                        ->visible(fn (Get $get): bool => $get('modo') === 'periodico'),

                    Forms\Components\TextInput::make('dia')
                        ->label('Dia do mês')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->required(fn (Get $get): bool => $get('modo') === 'periodico')
                        ->visible(fn (Get $get): bool => $get('modo') === 'periodico'),

                    Forms\Components\Toggle::make('ate_final_projeto')
                        ->label('Até o final do projeto')
                        ->default(false)
                        ->reactive()
                        ->visible(fn (Get $get): bool => $get('modo') === 'periodico'),

                    Forms\Components\TextInput::make('repeticoes')
                        ->label('Quantidade de repetições')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(120)
                        ->required(fn (Get $get): bool => $get('modo') === 'periodico' && !$get('ate_final_projeto'))
                        ->visible(fn (Get $get): bool => $get('modo') === 'periodico' && !$get('ate_final_projeto')),

                    Forms\Components\Repeater::make('datas_personalizadas')
                        ->label('Datas')
                        ->schema([
                            Forms\Components\DatePicker::make('data')
                                ->label('Data')
                                ->displayFormat('d/m/Y')
                                ->native()
                                ->required(),
                        ])
                        ->columns(1)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->visible(fn (Get $get): bool => $get('modo') === 'personalizado')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $datas = [];

                    if ($data['modo'] === 'periodico') {
                        if (!empty($data['ate_final_projeto'])) {
                            $dataEncerramento = $this->record->meta?->projeto?->data_encerramento;
                            if ($dataEncerramento) {
                                $datas = RecorrenciaDateGenerator::gerarAteData(
                                    $data['frequencia'],
                                    $data['inicio'],
                                    (int) $data['dia'],
                                    $dataEncerramento
                                );
                            }
                        } else {
                            $datas = RecorrenciaDateGenerator::gerar(
                                $data['frequencia'],
                                $data['inicio'],
                                (int) $data['dia'],
                                (int) $data['repeticoes']
                            );
                        }
                    } else {
                        $datas = collect($data['datas_personalizadas'] ?? [])
                            ->pluck('data')
                            ->filter()
                            ->map(fn ($d) => \Carbon\Carbon::parse($d))
                            ->sort()
                            ->values()
                            ->all();
                    }

                    if (!empty($datas)) {
                        $this->record->ocorrencias()->createMany(
                            collect($datas)->map(fn ($d) => ['data_fim' => $d])->all()
                        );

                        $this->record->update(['data_fim' => $datas[0]]);
                    }

                    $this->fillForm();
                })
                ->modalHeading('Adicionar Ocorrências')
                ->modalSubmitActionLabel('Criar Ocorrências'),

            Actions\DeleteAction::make(),
        ];
    }

    private function resolveProjeto(): ?Projeto
    {
        if ($this->record?->meta?->projeto) {
            return $this->record->meta->projeto;
        }

        $metaId = request()->query('meta_id');
        if (!$metaId) {
            return null;
        }

        $meta = Meta::with('projeto')->find($metaId);
        return $meta?->projeto;
    }
}
