<?php

namespace App\Notifications;

use App\Models\EtapaPrestacao;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrestacaoAprovada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public EtapaPrestacao $etapa,
        public User $aprovadoPor,
    ) {
        $this->etapa->loadMissing('projeto', 'projetoFinanciador.financiador');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        $body = "{$this->getDescricao()} | {$this->getProjetoNome()} | Aprovado por {$this->aprovadoPor->name}";

        return FilamentNotification::make()
            ->title('Prestação aprovada')
            ->body($body)
            ->icon('heroicon-o-check-badge')
            ->success()
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
            ->subject("[SGP] Prestação aprovada - {$projeto}")
            ->greeting("Olá, {$notifiable->name}!")
            ->line("A prestação \"{$this->getDescricao()}\" foi aprovada.")
            ->line("Aprovado por: {$this->aprovadoPor->name}")
            ->line("Projeto: {$projeto}")
            ->action('Ver no sistema', $this->getUrl())
            ->line('Acesse o sistema para acompanhar.');
    }

    private function getProjetoNome(): string
    {
        return $this->etapa->projeto?->nome ?? 'Projeto';
    }

    private function getDescricao(): string
    {
        $tipo = $this->etapa->tipo_label ?? $this->etapa->tipo;

        return trim("{$this->etapa->descricao} ({$tipo})");
    }

    private function getUrl(): string
    {
        $projetoId = $this->etapa->projeto_id;

        if (!$projetoId) {
            return url('/admin/projetos');
        }

        return url("/admin/projetos/{$projetoId}/cronograma-prestacao");
    }
}
