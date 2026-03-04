<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\TarefaOcorrencia;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaPrazoProximo extends Notification
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public int $diasRestantes,
        public ?TarefaOcorrencia $ocorrencia = null,
    ) {
        $this->tarefa->loadMissing('meta.projeto');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "{$this->tarefa->descricao} | {$this->getProjetoNome()} | Prazo: {$this->getPrazoLabel()}";

        return FilamentNotification::make()
            ->title($this->getTitulo())
            ->body($body)
            ->icon('heroicon-o-bell-alert')
            ->warning()
            ->actions([
                Action::make('ver')
                    ->label('Ver')
                    ->url($this->getUrl(), shouldOpenInNewTab: true)
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[SGP] {$this->getTitulo()} - {$this->getProjetoNome()}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A tarefa \"{$this->tarefa->descricao}\" está próxima do vencimento.")
            ->line("Projeto: {$this->getProjetoNome()}")
            ->line("Prazo: {$this->getPrazoLabel()}")
            ->line($this->getMensagemDias())
            ->action('Ver no sistema', $this->getUrl());
    }

    private function getTitulo(): string
    {
        return $this->diasRestantes === 1
            ? 'Lembrete: tarefa vence amanhã'
            : "Lembrete: tarefa vence em {$this->diasRestantes} dias";
    }

    private function getMensagemDias(): string
    {
        return $this->diasRestantes === 1
            ? 'Falta 1 dia para o vencimento.'
            : "Faltam {$this->diasRestantes} dias para o vencimento.";
    }

    private function getPrazoLabel(): string
    {
        $data = $this->ocorrencia?->data_fim ?? $this->tarefa->data_fim;

        return $data?->format('d/m/Y') ?? '-';
    }

    private function getProjetoNome(): string
    {
        return $this->tarefa->meta?->projeto?->nome ?? 'Projeto';
    }

    private function getUrl(): string
    {
        $projetoId = $this->tarefa->meta?->projeto_id;

        if (!$projetoId) {
            return url('/admin/projetos');
        }

        return url("/admin/projetos/{$projetoId}/cronograma-operacional");
    }
}
