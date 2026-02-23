<?php

namespace App\Filament\Resources\ProjetoResource\Pages;

use App\Filament\Resources\ProjetoResource;
use App\Models\Polo;
use App\Models\Projeto;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListProjetos extends ListRecords
{
    protected static string $resource = ProjetoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('duplicar')
                ->label('Duplicar Projeto')
                ->icon('heroicon-o-document-duplicate')
                ->visible(fn () => auth()->user()?->can('create', Projeto::class))
                ->modalHeading('Duplicar Projeto')
                ->modalSubmitActionLabel('Duplicar')
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Select::make('projeto_origem_id')
                        ->label('Projeto base')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->options(fn () => Projeto::query()->orderBy('nome')->pluck('nome', 'id')),

                    Forms\Components\TextInput::make('nome')
                        ->label('Novo nome do projeto')
                        ->required()
                        ->maxLength(200),

                    Forms\Components\DatePicker::make('data_inicio')
                        ->label('Nova data de início')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->native(),

                    Forms\Components\DatePicker::make('data_encerramento')
                        ->label('Nova data de encerramento')
                        ->required()
                        ->displayFormat('d/m/Y')
                        ->native(),

                    Forms\Components\Select::make('polos')
                        ->label('Novos polos de execução')
                        ->required()
                        ->multiple()
                        ->options(fn () => Polo::where('ativo', true)->pluck('nome', 'id'))
                        ->columnSpanFull(),
                ])
                ->action(function (array $data): void {
                    $novo = DB::transaction(function () use ($data) {
                        $origem = Projeto::with([
                            'contratos',
                            'etapasPrestacao',
                            'metas.tarefas.responsaveis',
                            'metas.tarefas.ocorrencias',
                        ])->findOrFail($data['projeto_origem_id']);

                        $novo = Projeto::create([
                            'nome' => $data['nome'],
                            'descricao' => $origem->descricao,
                            'descricao_breve' => $origem->descricao_breve,
                            'data_inicio' => $data['data_inicio'],
                            'data_encerramento' => $data['data_encerramento'],
                            'encerramento_contratos' => $origem->encerramento_contratos,
                            'data_limite_prorrogacao_contrato' => $origem->data_limite_prorrogacao_contrato,
                            'data_aditivo' => $origem->data_aditivo,
                            'status' => $origem->status,
                            'observacoes' => $origem->observacoes,
                        ]);

                        $novo->polos()->sync($data['polos']);

                        $contratoMap = [];
                        foreach ($origem->contratos as $contrato) {
                            $novoContrato = $contrato->replicate();
                            $novoContrato->projeto_id = $novo->id;
                            $novoContrato->save();
                            $contratoMap[$contrato->id] = $novoContrato->id;
                        }

                        $prestacaoGrupoMap = [];
                        foreach ($origem->etapasPrestacao as $etapa) {
                            $novaEtapa = $etapa->replicate();
                            $novaEtapa->projeto_id = $novo->id;
                            if ($etapa->projeto_financiador_id) {
                                $novaEtapa->projeto_financiador_id = $contratoMap[$etapa->projeto_financiador_id] ?? null;
                            }
                            if ($etapa->prestacao_grupo_id) {
                                $prestacaoGrupoMap[$etapa->prestacao_grupo_id] ??= (string) Str::uuid();
                                $novaEtapa->prestacao_grupo_id = $prestacaoGrupoMap[$etapa->prestacao_grupo_id];
                            }
                            $novaEtapa->status = 'pendente';
                            $novaEtapa->data_envio = null;
                            $novaEtapa->validado_por = null;
                            $novaEtapa->validado_em = null;
                            $novaEtapa->observacoes = null;
                            $novaEtapa->save();
                        }

                        $tarefaGrupoMap = [];
                        foreach ($origem->metas as $meta) {
                            $novaMeta = $meta->replicate();
                            $novaMeta->projeto_id = $novo->id;
                            $novaMeta->status = 'a_iniciar';
                            $novaMeta->save();

                            foreach ($meta->tarefas as $tarefa) {
                                $novaTarefa = $tarefa->replicate();
                                $novaTarefa->meta_id = $novaMeta->id;
                                if ($tarefa->tarefa_grupo_id) {
                                    $tarefaGrupoMap[$tarefa->tarefa_grupo_id] ??= (string) Str::uuid();
                                    $novaTarefa->tarefa_grupo_id = $tarefaGrupoMap[$tarefa->tarefa_grupo_id];
                                }
                                $novaTarefa->polo_id = null;
                                $novaTarefa->status = 'a_iniciar';
                                $novaTarefa->link_comprovacao = null;
                                $novaTarefa->comprovacao_validada = false;
                                $novaTarefa->validado_por = null;
                                $novaTarefa->validado_em = null;
                                $novaTarefa->observacoes = null;
                                $novaTarefa->save();

                                $responsaveis = $tarefa->responsaveis->pluck('id')->all();
                                if (!empty($responsaveis)) {
                                    $novaTarefa->responsaveis()->sync($responsaveis);
                                }

                                if ($tarefa->ocorrencias->isNotEmpty()) {
                                    $novaTarefa->ocorrencias()->createMany(
                                        $tarefa->ocorrencias->map(fn ($ocorrencia) => [
                                            'data_fim' => $ocorrencia->data_fim,
                                            'status' => 'pendente',
                                            'comprovacao_validada' => false,
                                            'validado_por' => null,
                                            'validado_em' => null,
                                            'observacoes' => null,
                                        ])->all()
                                    );
                                }
                            }
                        }

                        return $novo;
                    });

                    Notification::make()
                        ->title('Projeto duplicado')
                        ->body('O projeto foi duplicado com sucesso.')
                        ->success()
                        ->send();

                    $this->redirect(ProjetoResource::getUrl('edit', ['record' => $novo]));
                }),
        ];
    }
}
