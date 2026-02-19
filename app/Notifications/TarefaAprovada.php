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

class TarefaAprovada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $aprovadoPor,
    ) {
        $this->tarefa->loadMissing('meta.projeto');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $comRessalvas = $this->tarefa->status === 'com_ressalvas';
        $titulo = $comRessalvas ? 'Tarefa validada com ressalva' : 'Tarefa aprovada';
        $body = "{$this->tarefa->descricao} | {$this->getProjetoNome()} | Aprovado por {$this->aprovadoPor->name}";

        $notification = FilamentNotification::make()
            ->title($titulo)
            ->body($body)
            ->icon('heroicon-o-check-circle')
            ->actions([
                Action::make('ver')
                    ->label('Ver')
                    ->url($this->getUrl(), shouldOpenInNewTab: true)
                    ->markAsRead(),
            ]);

        if ($comRessalvas) {
            $notification->warning();
        } else {
            $notification->success();
        }

        return $notification->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $projeto = $this->getProjetoNome();
        $comRessalvas = $this->tarefa->status === 'com_ressalvas';
        $assunto = $comRessalvas ? 'Tarefa validada com ressalva' : 'Tarefa aprovada';
        $linha = $comRessalvas
            ? "A tarefa \"{$this->tarefa->descricao}\" foi validada com ressalva."
            : "A tarefa \"{$this->tarefa->descricao}\" foi aprovada.";

        return (new MailMessage)
            ->subject("[SGP] {$assunto} - {$projeto}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line($linha)
            ->line("Aprovado por: {$this->aprovadoPor->name}")
            ->line("Projeto: {$projeto}")
            ->action('Ver no sistema', $this->getUrl())
            ->line('Acesse o sistema para acompanhar.');
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
