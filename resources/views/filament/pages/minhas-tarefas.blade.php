<x-filament-panels::page>
    @php
        $resumo = $this->getResumo();
    @endphp

    {{-- Cards de resumo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $resumo['total'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Pendentes</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $resumo['pendentes'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Em Análise</div>
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $resumo['em_analise'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">Realizadas</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $resumo['realizadas'] }}</div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 mb-6">
        <form method="get" class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-400" for="year">Ano</label>
            <select id="year" name="year" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                @foreach($this->getAnosOptions() as $value => $label)
                    <option value="{{ $value }}" @selected($value == $this->year)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="month">Mês</label>
            <select id="month" name="month" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getMesesOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($key === $this->month)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="status">Status</label>
            <select id="status" name="status" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getStatusOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($key === $this->statusFilter)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="projeto_id">Projeto</label>
            <select id="projeto_id" name="projeto_id" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getProjetosOptions() as $id => $nome)
                    <option value="{{ $id }}" @selected((string) $id === (string) $this->projetoFilter)>{{ $nome }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Cards mensais --}}
    @php
        $mensal = $this->getTarefasMensal();
    @endphp

    <div class="space-y-6">
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
                                        'com_ressalvas' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                                    {{ $tarefa['status_label'] }}
                                </span>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Projeto:
                                <a
                                    href="{{ \App\Filament\Resources\ProjetoResource::getUrl('cronograma-operacional', ['record' => $tarefa['projeto_id']]) }}"
                                    class="text-primary-600 dark:text-primary-400 hover:underline font-medium"
                                >
                                    {{ $tarefa['projeto_nome'] }}
                                </a>
                                &middot; Meta: {{ $tarefa['meta'] }}
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
                Nenhuma tarefa encontrada para os filtros selecionados.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
