<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-start gap-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->record->nome }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Cronograma de Metas/Tarefas</p>
            </div>
            <div class="text-right text-sm text-gray-600 dark:text-gray-400">
                Polos:
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ $this->record->polos->pluck('nome')->join(', ') ?: 'Geral' }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 mb-6">
        <form method="get" class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-400" for="year">Ano</label>
            <select id="year" name="year" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                @foreach($this->getAnos() as $ano)
                    <option value="{{ $ano }}" @selected($ano == $this->year)>{{ $ano }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="month">Mês</label>
            <select id="month" name="month" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getMesesOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($key === $this->month)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="meta_id">Meta</label>
            <select id="meta_id" name="meta_id" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todas</option>
                @foreach($this->getMetasOptions() as $id => $label)
                    <option value="{{ $id }}" @selected((string) $id === (string) $this->metaId)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @php
        $meses = $this->getMeses();
        $linhas = $this->getLinhas();
        $mensal = $this->getTarefasMensal();
    @endphp

    <div x-data="{ view: 'mensal' }" class="space-y-6">
        <div class="flex justify-end">
            <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                <button
                    type="button"
                    class="px-3 py-1.5 text-sm font-medium"
                    :class="view === 'lista' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                    x-on:click="view = 'lista'"
                >
                    Lista
                </button>
                <button
                    type="button"
                    class="px-3 py-1.5 text-sm font-medium border-l border-gray-200 dark:border-gray-700"
                    :class="view === 'mensal' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300'"
                    x-on:click="view = 'mensal'"
                >
                    Mensal
                </button>
            </div>
        </div>

        <div x-show="view === 'lista'" x-transition class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-separate border-spacing-y-2">
                    <thead>
                        <tr class="border-b dark:border-gray-700">
                            <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-80 max-w-[20rem]">Ações</th>
                            @foreach($meses as $mes)
                                <th class="text-center py-2 px-2 font-medium text-gray-600 dark:text-gray-400 w-14">{{ $mes['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($linhas as $meta)
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <td class="py-2 px-3 font-semibold text-gray-900 dark:text-white" colspan="{{ 1 + count($meses) }}">
                                    {{ $meta['meta'] }}
                                </td>
                            </tr>
                            @foreach($meta['tarefas'] as $tarefa)
                                <tr class="bg-gray-50/70 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50">
                                    <td class="py-2 px-3 text-gray-900 dark:text-white align-top w-80 max-w-[20rem]">
                                        <div class="whitespace-normal break-words overflow-hidden">
                                            {{ $tarefa['descricao'] }}
                                        </div>
                                    </td>
                                    @foreach($meses as $mes)
                                        @php $val = $tarefa['marcacoes'][$mes['key']] ?? '' @endphp
                                        <td class="py-2 px-2 text-center">
                                            @if($val)
                                                <span
                                                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 text-xs font-bold"
                                                    title="Período: {{ $tarefa['periodo'] }}"
                                                >
                                                    {{ $val }}
                                                </span>
                                            @else
                                                <span class="text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td class="py-6 text-center text-gray-500 dark:text-gray-400" colspan="{{ 1 + count($meses) }}">
                                    Nenhuma meta ou tarefa cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="view === 'mensal'" x-transition class="space-y-6">
            @forelse($mensal as $mes)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $mes['label'] }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $mes['items']->count() }} tarefas
                        </div>
                    </div>
                    <div class="space-y-3">
                        @forelse($mes['items'] as $tarefa)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-base font-semibold text-gray-900 dark:text-white">
                                        {{ $tarefa['descricao'] }}
                                    </div>
                                    @php
                                        $statusClass = match($tarefa['status_normalizado'] ?? null) {
                                            'pendente' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                            'em_execucao' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                            'em_analise' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                            'devolvido' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                            'realizado' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                                        {{ $tarefa['status_label'] }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Meta: {{ $tarefa['meta'] }}
                                </div>
                                <div class="mt-3 flex flex-wrap gap-6 text-sm text-gray-700 dark:text-gray-300">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Prazo:</span>
                                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 font-semibold">
                                            {{ $tarefa['prazo'] ? \Carbon\Carbon::parse($tarefa['prazo'])->format('d/m/Y') : '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Polo:</span>
                                        {{ $tarefa['polo'] }}
                                    </div>
                                    <div class="flex items-center gap-1">
                                        @php
                                            $currentUser = auth()->user();
                                            $podeValidar = $currentUser?->isAdminGeral() || $currentUser?->isDiretorProjetos();
                                            $podeEnviar = $currentUser?->isSuperAdmin() || $currentUser?->isCoordenadorPolo() || $currentUser?->isDiretorOperacoes();
                                            $status = $tarefa['status'] ?? '';
                                        @endphp

                                            @if($status === 'em_analise')
                                                @if($podeValidar)
                                                    <x-filament::button
                                                        size="sm"
                                                        color="warning"
                                                        wire:click="mountAction('analisarTarefa', { tarefa_id: {{ $tarefa['id'] }}, tarefa_ocorrencia_id: {{ $tarefa['ocorrencia_id'] ?? 'null' }} })"
                                                    >
                                                        Analisar
                                                    </x-filament::button>
                                                @else
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    Aguardando validação
                                                </span>
                                            @endif
                                        @elseif($status === 'realizado' || $status === 'concluido' || $status === 'com_ressalvas')
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                {{ $status === 'com_ressalvas' ? 'Validado com ressalva' : 'Validado' }}
                                            </span>
                                            @elseif($status === 'devolvido')
                                                @if($podeEnviar)
                                                    <x-filament::button
                                                        size="sm"
                                                        color="primary"
                                                        wire:click="mountAction('realizarTarefa', { tarefa_id: {{ $tarefa['id'] }}, tarefa_ocorrencia_id: {{ $tarefa['ocorrencia_id'] ?? 'null' }} })"
                                                    >
                                                        Reenviar
                                                    </x-filament::button>
                                                @endif
                                            @else
                                                @if($podeEnviar)
                                                    <x-filament::button
                                                        size="sm"
                                                        color="primary"
                                                        wire:click="mountAction('realizarTarefa', { tarefa_id: {{ $tarefa['id'] }}, tarefa_ocorrencia_id: {{ $tarefa['ocorrencia_id'] ?? 'null' }} })"
                                                    >
                                                        Enviar
                                                    </x-filament::button>
                                                @endif
                                        @endif

                                        @if($tarefa['tem_historico'] ?? false)
                                            <x-filament::button
                                                size="sm"
                                                color="gray"
                                                wire:click="mountAction('historicoTarefa', { tarefa_id: {{ $tarefa['id'] }}, tarefa_ocorrencia_id: {{ $tarefa['ocorrencia_id'] ?? 'null' }} })"
                                            >
                                                Histórico
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-500 dark:text-gray-400">
                                Nenhuma tarefa para este mês.
                            </div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma meta ou tarefa cadastrada.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
