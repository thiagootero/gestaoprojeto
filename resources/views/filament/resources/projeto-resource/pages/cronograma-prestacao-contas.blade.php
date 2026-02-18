<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->record->nome }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Cronograma de Prestação de Contas</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 mb-6">
        <form method="get" class="flex flex-wrap items-center gap-3">
            <label class="text-sm text-gray-600 dark:text-gray-400" for="origem">Origem</label>
            <select id="origem" name="origem" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todas</option>
                <option value="interna" @selected($this->origem === 'interna')>Interna</option>
                <option value="financiador" @selected($this->origem === 'financiador')>Financiador</option>
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="financiador_id">Financiador</label>
            <select id="financiador_id" name="financiador_id" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getFinanciadoresOptions() as $id => $label)
                    <option value="{{ $id }}" @selected((string) $id === (string) $this->financiadorId)>{{ $label }}</option>
                @endforeach
            </select>

            <label class="text-sm text-gray-600 dark:text-gray-400" for="month">Mês</label>
            <select id="month" name="month" class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800" onchange="this.form.submit()">
                <option value="">Todos</option>
                @foreach($this->getMesesOptions() as $key => $label)
                    <option value="{{ $key }}" @selected($key === $this->month)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @php
        $resumo = $this->getPrestacaoResumo();
        $mensal = $this->getPrestacaoMensal();
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

        <div x-show="view === 'lista'" x-transition class="space-y-6">
            {{-- Internas --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-semibold text-gray-900 dark:text-white">Interna</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $resumo['internas']['qualitativas']->count() + $resumo['internas']['quantitativas']->count() }} itens
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-40">Tipo</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-40">Data Limite</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-32">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($resumo['internas']['qualitativas']->merge($resumo['internas']['quantitativas'])->sortBy('data_limite') as $etapa)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $etapa->descricao ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 rounded text-xs {{ $etapa->tipo === 'qualitativa' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' }}">
                                            {{ $etapa->tipo_label }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $this->formatData($etapa->data_limite) }}</td>
                                <td class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                    <span @class([
                                        'px-2 py-1 rounded text-xs font-medium',
                                        'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $etapa->status_color === 'gray',
                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $etapa->status_color === 'warning',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $etapa->status_color === 'info',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' => $etapa->status_color === 'primary',
                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $etapa->status_color === 'success',
                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $etapa->status_color === 'danger',
                                    ])>
                                        {{ $etapa->status_label }}
                                    </span>
                                </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma prestação interna cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Financiadores --}}
            @forelse($resumo['financiadores'] as $financiador => $dados)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $financiador }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $dados['qualitativas']->count() + $dados['quantitativas']->count() }} itens
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-40">Tipo</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-40">Data Limite</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-32">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dados['qualitativas']->merge($dados['quantitativas'])->sortBy('data_limite') as $etapa)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $etapa->descricao ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-1 rounded text-xs {{ $etapa->tipo === 'qualitativa' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' }}">
                                                {{ $etapa->tipo_label }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $this->formatData($etapa->data_limite) }}</td>
                                    <td class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $etapa->status_color === 'gray',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $etapa->status_color === 'warning',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $etapa->status_color === 'info',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' => $etapa->status_color === 'primary',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $etapa->status_color === 'success',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $etapa->status_color === 'danger',
                                        ])>
                                            {{ $etapa->status_label }}
                                        </span>
                                    </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                            Nenhuma prestação cadastrada para este financiador.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma prestação de contas cadastrada.
                </div>
            @endforelse
        </div>

        <div x-show="view === 'mensal'" x-transition class="space-y-6">
            @forelse($mensal as $mes)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $mes['label'] }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $mes['items']->count() }} itens
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-28">Origem</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-48">Financiador</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-32">Tipo</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-32">Data Limite</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-28">Status</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-48">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mes['items'] as $etapa)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="py-2 px-3 text-gray-900 dark:text-white">
                                            {{ $etapa->origem === 'financiador' ? 'Financiador' : 'Interna' }}
                                        </td>
                                        <td class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                            {{ $etapa->projetoFinanciador?->financiador?->nome ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $etapa->descricao ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-1 rounded text-xs {{ $etapa->tipo === 'qualitativa' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' }}">
                                                {{ $etapa->tipo_label }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-gray-700 dark:text-gray-300">{{ $this->formatData($etapa->data_limite) }}</td>
                                        <td class="py-2 px-3 text-gray-700 dark:text-gray-300">
                                            @php
                                                $statusClass = match($etapa->getStatusNormalizado()) {
                                                    'pendente' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                                                    'em_execucao' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                    'em_analise' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                    'devolvido' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
                                                    'realizado' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 rounded text-xs font-medium {{ $statusClass }}">
                                                {{ $etapa->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3">
                                            @php
                                                $currentUser = auth()->user();
                                                $podeEnviar = true;
                                                $podeValidar = $currentUser?->isAdminGeral() || $currentUser?->isDiretorOperacoes() || $currentUser?->isDiretorProjetos();
                                                $temHistorico = $etapa->realizacoes->count() > 0 || $etapa->observacoes || $etapa->validadoPorUser;

                                                if ($currentUser?->isCoordenadorFinanceiro() && $etapa->tipo !== 'financeira') {
                                                    $podeEnviar = false;
                                                }
                                                if ($currentUser?->isCoordenadorPolo() && $etapa->tipo !== 'qualitativa') {
                                                    $podeEnviar = false;
                                                }
                                            @endphp
                                            <div class="flex items-center gap-1">
                                                @if($etapa->status === 'em_analise')
                                                    @if($podeValidar)
                                                        <x-filament::button
                                                            size="sm"
                                                            color="warning"
                                                            wire:click="mountAction('analisarPrestacao', { etapa_id: {{ $etapa->id }} })"
                                                        >
                                                            Analisar
                                                        </x-filament::button>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                            Aguardando validação
                                                        </span>
                                                    @endif
                                                @elseif($etapa->status === 'devolvido')
                                                    @if($podeEnviar)
                                                        <x-filament::button
                                                            size="sm"
                                                            color="primary"
                                                            wire:click="mountAction('realizarPrestacao', { etapa_id: {{ $etapa->id }} })"
                                                        >
                                                            Reenviar
                                                        </x-filament::button>
                                                    @endif
                                                @elseif($etapa->getStatusNormalizado() === 'realizado')
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                        Validado
                                                    </span>
                                                @elseif($podeEnviar)
                                                    <x-filament::button
                                                        size="sm"
                                                        color="primary"
                                                        wire:click="mountAction('realizarPrestacao', { etapa_id: {{ $etapa->id }} })"
                                                    >
                                                        Enviar
                                                    </x-filament::button>
                                                @endif

                                                @if($temHistorico)
                                                    <x-filament::button
                                                        size="sm"
                                                        color="gray"
                                                        wire:click="mountAction('historicoPrestacao', { etapa_id: {{ $etapa->id }} })"
                                                    >
                                                        Histórico
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                            Nenhuma prestação para este mês.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma prestação de contas cadastrada.
                </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
