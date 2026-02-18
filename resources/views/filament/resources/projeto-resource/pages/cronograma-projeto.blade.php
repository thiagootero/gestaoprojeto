<x-filament-panels::page>
    {{-- Informações do Projeto --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->record->nome }}</h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $this->record->descricao }}</p>
            </div>
            <div class="text-right">
                <span @class([
                    'px-3 py-1 rounded-full text-sm font-medium',
                    'bg-gray-100 text-gray-800' => $this->record->status === 'planejamento',
                    'bg-green-100 text-green-800' => $this->record->status === 'em_execucao',
                    'bg-yellow-100 text-yellow-800' => $this->record->status === 'suspenso',
                    'bg-blue-100 text-blue-800' => $this->record->status === 'encerrado',
                    'bg-purple-100 text-purple-800' => $this->record->status === 'prestacao_final',
                ])>
                    {{ $this->record->status_label }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t dark:border-gray-700">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Início</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->record->data_inicio->format('d/m/Y') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Encerramento</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $this->record->data_encerramento->format('d/m/Y') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Progresso Geral</span>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $this->record->percentual_conclusao }}%"></div>
                    </div>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $this->record->percentual_conclusao }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Prestações de Contas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <x-heroicon-o-document-text class="w-5 h-5" />
            Prestações de Contas
        </h3>

        @php
            $prestacoes = $this->getPrestacoes();
        @endphp

        {{-- Internas --}}
        <div>
            <div class="border rounded-lg border-gray-200 dark:border-gray-700">
                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                    <div class="font-semibold text-gray-900 dark:text-white">Internas</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $prestacoes['internas']->count() }} etapas</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Tipo</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Data Limite</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Dias</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($prestacoes['internas'] as $prestacao)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $prestacao['descricao'] ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $prestacao['tipo'] === 'qualitativa',
                                            'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' => $prestacao['tipo'] === 'financeira',
                                        ])>
                                            {{ $prestacao['tipo_label'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $prestacao['data_limite'] ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $prestacao['urgencia_color'] === 'success',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $prestacao['urgencia_color'] === 'warning',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $prestacao['urgencia_color'] === 'danger',
                                        ])>
                                            {{ $prestacao['dias_restantes'] < 0 ? abs($prestacao['dias_restantes']) . ' dias atrasado' : $prestacao['dias_restantes'] . ' dias' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $prestacao['status_color'] === 'gray',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $prestacao['status_color'] === 'warning',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $prestacao['status_color'] === 'info',
                                            'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' => $prestacao['status_color'] === 'primary',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $prestacao['status_color'] === 'success',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $prestacao['status_color'] === 'danger',
                                        ])>
                                            {{ $prestacao['status_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">
                                        Nenhuma prestação interna cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Por Financiador --}}
        <div class="space-y-4 mt-6">
            @forelse($prestacoes['por_financiador'] as $financiador => $itens)
                <div class="border rounded-lg border-gray-200 dark:border-gray-700">
                    <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/50 flex items-center justify-between">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ $financiador }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $itens->count() }} etapas</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b dark:border-gray-700">
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Tipo</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Data Limite</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Dias</th>
                                    <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($itens as $prestacao)
                                    <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                        <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $prestacao['descricao'] ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            <span @class([
                                                'px-2 py-1 rounded text-xs font-medium',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $prestacao['tipo'] === 'qualitativa',
                                                'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300' => $prestacao['tipo'] === 'financeira',
                                            ])>
                                                {{ $prestacao['tipo_label'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-gray-900 dark:text-white">{{ $prestacao['data_limite'] ?? '-' }}</td>
                                        <td class="py-2 px-3">
                                            <span @class([
                                                'px-2 py-1 rounded text-xs font-medium',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $prestacao['urgencia_color'] === 'success',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $prestacao['urgencia_color'] === 'warning',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $prestacao['urgencia_color'] === 'danger',
                                        ])>
                                                {{ $prestacao['dias_restantes'] < 0 ? abs($prestacao['dias_restantes']) . ' dias atrasado' : $prestacao['dias_restantes'] . ' dias' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3">
                                            <span @class([
                                                'px-2 py-1 rounded text-xs font-medium',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $prestacao['status_color'] === 'gray',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $prestacao['status_color'] === 'warning',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $prestacao['status_color'] === 'info',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' => $prestacao['status_color'] === 'primary',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $prestacao['status_color'] === 'success',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $prestacao['status_color'] === 'danger',
                                            ])>
                                                {{ $prestacao['status_label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="py-4 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma prestação de contas de financiadores cadastrada.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Metas e Tarefas --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <x-heroicon-o-flag class="w-5 h-5" />
            Metas e Tarefas
        </h3>

        @forelse($this->getMetas() as $meta)
            <div x-data="{ open: false }" class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
                {{-- Header da Meta --}}
                <div class="p-4 bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300 px-3 py-1 rounded-full text-sm font-bold">
                                Meta {{ $meta['numero'] }}
                            </span>
                            <span @class([
                                'px-2 py-1 rounded text-xs font-medium',
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $meta['status_color'] === 'gray',
                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $meta['status_color'] === 'info',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $meta['status_color'] === 'success',
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $meta['status_color'] === 'danger',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $meta['status_color'] === 'warning',
                            ])>
                                {{ $meta['status_label'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $meta['tarefas_concluidas'] }}/{{ $meta['total_tarefas'] }} tarefas
                            </span>
                            <div class="w-24 bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $meta['percentual'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $meta['percentual'] }}%</span>
                            <button
                                type="button"
                                class="ml-2 inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                                x-on:click="open = !open"
                            >
                                <span x-text="open ? 'Recolher' : 'Expandir'"></span>
                                <x-heroicon-o-chevron-down class="w-4 h-4 transition-transform"
                                    x-bind:class="open ? '' : '-rotate-90'" />
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-gray-700 dark:text-gray-300">{{ $meta['descricao'] }}</p>
                </div>

                {{-- Tarefas --}}
                <div x-show="open" x-transition class="overflow-x-auto">
                    <table class="w-full text-sm bg-white dark:bg-gray-800">
                        <thead>
                            <tr class="border-b dark:border-gray-700 bg-white dark:bg-gray-800">
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-16">Nº</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400">Descrição</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-24">Polo</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-40">Responsável</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-24">Início</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-24">Prazo</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600 dark:text-gray-400 w-28">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($meta['tarefas'] as $tarefa)
                                <tr @class([
                                    'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50',
                                    'bg-red-50 dark:bg-red-900/20' => $tarefa['atrasada'],
                                    'bg-yellow-50 dark:bg-yellow-900/20' => $tarefa['vencendo'] && !$tarefa['atrasada'],
                                ])>
                                    <td class="py-2 px-3 font-mono text-gray-600 dark:text-gray-400">{{ $tarefa['numero'] }}</td>
                                    <td class="py-2 px-3 text-gray-900 dark:text-white">{{ \Str::limit($tarefa['descricao'], 60) }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">
                                            {{ $tarefa['polo'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-400 text-xs">{{ \Str::limit($tarefa['responsavel'], 25) }}</td>
                                    <td class="py-2 px-3 text-gray-600 dark:text-gray-400">{{ $tarefa['data_inicio'] ?? '-' }}</td>
                                    <td class="py-2 px-3">
                                        @if($tarefa['atrasada'])
                                            <span class="text-red-600 dark:text-red-400 font-medium">{{ $tarefa['data_fim'] }}</span>
                                        @elseif($tarefa['vencendo'])
                                            <span class="text-yellow-600 dark:text-yellow-400 font-medium">{{ $tarefa['data_fim'] }}</span>
                                        @else
                                            <span class="text-gray-600 dark:text-gray-400">{{ $tarefa['data_fim'] ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3">
                                        <span @class([
                                            'px-2 py-1 rounded text-xs font-medium',
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' => $tarefa['status_color'] === 'gray',
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' => $tarefa['status_color'] === 'info',
                                            'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' => $tarefa['status_color'] === 'success',
                                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' => $tarefa['status_color'] === 'danger',
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' => $tarefa['status_color'] === 'warning',
                                        ])>
                                            {{ $tarefa['status_label'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-8 text-center text-gray-500 dark:text-gray-400">
                Nenhuma meta cadastrada para este projeto.
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
