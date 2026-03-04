<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Support\TarefaPrazoReminderService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notificar:tarefas-prazo', function (TarefaPrazoReminderService $service) {
    $resultado = $service->enviarLembretes([7, 1]);

    $this->info("Lembretes enviados: {$resultado['sent']}");
    $this->info("Tarefas verificadas: {$resultado['tasks']}");
})->purpose('Envia lembretes de tarefas a vencer em 7 dias e 1 dia');
