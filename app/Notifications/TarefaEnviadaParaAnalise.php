<?php

namespace App\Notifications;

use App\Models\Tarefa;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TarefaEnviadaParaAnalise extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $enviadoPor,
    ) {
        $this->tarefa->loadMissing('meta.projeto');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "{$this->tarefa->descricao} | {$this->getProjetoNome()} | Enviado por {$this->enviadoPor->name}";

        return FilamentNotification::make()
            ->title('Tarefa enviada para análise')
            ->body($body)
            ->icon('heroicon-o-clipboard-document-check')
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
        $projeto = $this->getProjetoNome();

        return (new MailMessage)
            ->subject("[SGP] Tarefa enviada para análise - {$projeto}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A tarefa \"{$this->tarefa->descricao}\" foi enviada para análise.")
            ->line("Enviado por: {$this->enviadoPor->name}")
            ->line("Projeto: {$projeto}")
            ->action('Ver no sistema', $this->getUrl())
            ->line('Acesse o sistema para analisar.');
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
