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
        $comRessalvas = $this->etapa->status === 'com_ressalvas';
        $titulo = $comRessalvas ? 'Prestação validada com ressalva' : 'Prestação aprovada';
        $body = "{$this->getDescricao()} | {$this->getProjetoNome()} | Aprovado por {$this->aprovadoPor->name}";

        $notification = FilamentNotification::make()
            ->title($titulo)
            ->body($body)
            ->icon('heroicon-o-check-badge')
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
        $comRessalvas = $this->etapa->status === 'com_ressalvas';
        $assunto = $comRessalvas ? 'Prestação validada com ressalva' : 'Prestação aprovada';
        $linha = $comRessalvas
            ? "A prestação \"{$this->getDescricao()}\" foi validada com ressalva."
            : "A prestação \"{$this->getDescricao()}\" foi aprovada.";

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
