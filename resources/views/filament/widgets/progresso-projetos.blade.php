<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Progresso dos Projetos em Execução
        </x-slot>

        <div class="space-y-4">
            @forelse($this->getProjetos() as $projeto)
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex justify-between items-center mb-2">
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $projeto['nome'] }}
                        </span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $projeto['concluidas'] }}/{{ $projeto['total'] }} tarefas ({{ $projeto['percentual'] }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div class="{{ $projeto['cor'] }} h-4 rounded-full transition-all duration-300"
                             style="width: {{ $projeto['percentual'] }}%">
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    Nenhum projeto em execução no momento.
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
