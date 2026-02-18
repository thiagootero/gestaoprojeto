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

class TarefaDevolvida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $devolvidoPor,
        public string $motivo,
    ) {
        $this->tarefa->loadMissing('meta.projeto');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "{$this->tarefa->descricao} | {$this->getProjetoNome()} | Devolvido por {$this->devolvidoPor->name}";

        if ($this->motivo !== '') {
            $body .= " | Motivo: {$this->motivo}";
        }

        return FilamentNotification::make()
            ->title('Tarefa devolvida para ajuste')
            ->body($body)
            ->icon('heroicon-o-arrow-uturn-left')
            ->danger()
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

        $message = (new MailMessage)
            ->subject("[SGP] Tarefa devolvida - {$projeto}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A tarefa \"{$this->tarefa->descricao}\" foi devolvida para ajuste.")
            ->line("Devolvido por: {$this->devolvidoPor->name}")
            ->line("Projeto: {$projeto}");

        if ($this->motivo !== '') {
            $message->line("Motivo: {$this->motivo}");
        }

        return $message
            ->action('Ver no sistema', $this->getUrl())
            ->line('Acesse o sistema para corrigir e reenviar.');
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
