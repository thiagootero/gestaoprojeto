# Proposta: Central de Notificacoes - SGP Sementes do Vale

## Objetivo

Criar um sistema unificado de notificacoes que dispare **alertas dentro do sistema** (sino de notificacoes no painel) e **emails** automaticamente com base nos eventos do fluxo de trabalho.

---

## Arquitetura Proposta

### Stack Tecnica

- **Database Notifications** (Laravel built-in) - notificacoes no sino do painel
- **Mail Notifications** (Laravel built-in) - emails via SMTP/Mailgun/SES
- **Laravel Queues** - envio assincrono de emails para nao travar a interface
- **Filament Notifications** - integracao nativa com o sino do Filament

### Estrutura

```
app/
  Notifications/
    TarefaEnviadaParaAnalise.php
    TarefaAprovada.php
    TarefaDevolvida.php
    PrestacaoEnviadaParaAnalise.php
    PrestacaoAprovada.php
    PrestacaoDevolvida.php
    PrestacaoProximaDoVencimento.php
    TarefaProximaDoPrazo.php
  Listeners/
    EnviarNotificacaoTarefa.php
    EnviarNotificacaoPrestacao.php
  Events/
    TarefaStatusAlterado.php
    PrestacaoStatusAlterada.php
```

---

## Eventos e Destinatarios

### 1. Tarefas (Cronograma Operacional)

| Evento | Gatilho | Destinatarios | Canal |
|--------|---------|---------------|-------|
| Tarefa enviada para analise | Coordenador clica "Enviar" | admin_geral, diretor_projetos | Sistema + Email |
| Tarefa aprovada | Diretor/Admin clica "Aprovar" | Coordenador que enviou | Sistema + Email |
| Tarefa devolvida | Diretor/Admin clica "Devolver" | Coordenador que enviou | Sistema + Email |
| Tarefa proxima do prazo (7 dias) | Agendamento diario (Schedule) | Responsavel pela tarefa | Sistema + Email |
| Tarefa atrasada | Agendamento diario (Schedule) | Responsavel + diretor_projetos | Sistema + Email |

### 2. Prestacao de Contas

| Evento | Gatilho | Destinatarios | Canal |
|--------|---------|---------------|-------|
| Prestacao enviada para analise | Coordenador clica "Enviar" | admin_geral, diretor_projetos, diretor_operacoes | Sistema + Email |
| Prestacao aprovada | Diretor/Admin clica "Aprovar" | Coordenador que enviou | Sistema + Email |
| Prestacao devolvida | Diretor/Admin clica "Devolver" | Coordenador que enviou | Sistema + Email |
| Prestacao proxima do vencimento (15 dias) | Agendamento diario (Schedule) | Responsaveis pelo tipo (polo ou financeiro) | Sistema + Email |
| Prestacao proxima do vencimento (7 dias) | Agendamento diario (Schedule) | Responsaveis + diretores | Sistema + Email |
| Prestacao atrasada | Agendamento diario (Schedule) | Responsaveis + diretores + admin | Sistema + Email |

---

## Logica de Destinatarios por Evento

### Tarefa enviada para analise
```
Quem recebe:
  - Todos os usuarios com perfil admin_geral
  - Todos os usuarios com perfil diretor_projetos
Contexto: "[Projeto X] Tarefa 'Y' enviada para analise por [Nome do Coordenador]"
```

### Tarefa aprovada / devolvida
```
Quem recebe:
  - O usuario que fez o ultimo envio (PrestacaoRealizacao/TarefaRealizacao -> user_id)
Contexto aprovada: "[Projeto X] Tarefa 'Y' foi aprovada por [Nome do Diretor]"
Contexto devolvida: "[Projeto X] Tarefa 'Y' foi devolvida por [Nome do Diretor]. Motivo: ..."
```

### Prestacao enviada para analise
```
Quem recebe:
  - Todos os usuarios com perfil admin_geral
  - Todos os usuarios com perfil diretor_projetos
  - Todos os usuarios com perfil diretor_operacoes
Contexto: "[Projeto X] Prestacao 'Y' (financeira/qualitativa) enviada para analise por [Nome]"
```

### Prestacao aprovada / devolvida
```
Quem recebe:
  - O usuario que fez o ultimo envio
  - Se qualitativa: coordenadores_polo dos polos do projeto
  - Se financeira: coordenadores_financeiro responsaveis
Contexto aprovada: "[Projeto X] Prestacao 'Y' aprovada por [Nome]"
Contexto devolvida: "[Projeto X] Prestacao 'Y' devolvida por [Nome]. Motivo: ..."
```

### Alertas de prazo (agendados)
```
Quem recebe - Tarefas:
  - 7 dias antes: responsavel da tarefa
  - Atrasada: responsavel + diretor_projetos + admin_geral

Quem recebe - Prestacoes:
  - 15 dias antes: coordenadores do tipo (polo/financeiro)
  - 7 dias antes: coordenadores + diretores
  - Atrasada: todos os envolvidos + admin_geral
```

---

## Implementacao - Etapas

### Etapa 1: Infraestrutura Base

1. **Migration** para tabela `notifications` (Laravel padrao)
   ```
   php artisan notifications:table
   php artisan migrate
   ```

2. **Trait `Notifiable`** no model User (provavelmente ja existe)

3. **Configurar Filament DatabaseNotifications** no AdminPanelProvider
   ```php
   ->databaseNotifications()
   ->databaseNotificationsPolling('30s')
   ```

4. **Configurar fila** (queue) para envio assincrono de emails
   ```
   QUEUE_CONNECTION=database
   php artisan queue:table
   php artisan migrate
   ```

### Etapa 2: Notificacoes de Fluxo de Validacao

5. **Criar classes de Notification** para cada evento:
   - `TarefaEnviadaParaAnalise` (via database + mail)
   - `TarefaAprovada` (via database + mail)
   - `TarefaDevolvida` (via database + mail)
   - `PrestacaoEnviadaParaAnalise` (via database + mail)
   - `PrestacaoAprovada` (via database + mail)
   - `PrestacaoDevolvida` (via database + mail)

6. **Disparar notificacoes** nos pontos de acao existentes:
   - `CronogramaOperacional.php` -> `realizarTarefaAction()`, `analisarTarefaAction()`
   - `CronogramaPrestacaoContas.php` -> `realizarPrestacaoAction()`, `analisarPrestacaoAction()`
   - Widgets: `TarefasEmAnalise.php`, `PrestacoesEmAnalise.php`

### Etapa 3: Alertas Agendados de Prazo

7. **Criar Commands** para verificacao de prazos:
   - `app/Console/Commands/NotificarTarefasProximasPrazo.php`
   - `app/Console/Commands/NotificarPrestacoesProximasVencimento.php`

8. **Registrar no Schedule** (routes/console.php ou app/Console/Kernel.php):
   ```php
   Schedule::command('notificar:tarefas-prazo')->dailyAt('08:00');
   Schedule::command('notificar:prestacoes-vencimento')->dailyAt('08:00');
   ```

### Etapa 4: Preferencias do Usuario (Opcional)

9. **Migration** para tabela `notification_preferences`:
   - `user_id`
   - `tipo_notificacao` (ex: tarefa_aprovada, prestacao_devolvida)
   - `canal_email` (boolean, default true)
   - `canal_sistema` (boolean, default true)

10. **Pagina de preferencias** no perfil do usuario para ativar/desativar por tipo

---

## Exemplo de Classe de Notificacao

```php
// app/Notifications/TarefaEnviadaParaAnalise.php

class TarefaEnviadaParaAnalise extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Tarefa $tarefa,
        public User $enviadoPor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Tarefa enviada para analise')
            ->body("\"{$this->tarefa->descricao}\" enviada por {$this->enviadoPor->name}")
            ->icon('heroicon-o-clipboard-document-check')
            ->warning()
            ->getDatabaseMessage();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $projeto = $this->tarefa->meta?->projeto?->nome ?? 'Projeto';

        return (new MailMessage)
            ->subject("[SGP] Tarefa enviada para analise - {$projeto}")
            ->greeting("Ola, {$notifiable->name}!")
            ->line("A tarefa \"{$this->tarefa->descricao}\" foi enviada para analise.")
            ->line("Enviada por: {$this->enviadoPor->name}")
            ->line("Projeto: {$projeto}")
            ->action('Ver no Sistema', url("/admin/projetos/{$this->tarefa->meta?->projeto_id}/cronograma-operacional"))
            ->line('Acesse o sistema para analisar.');
    }
}
```

## Exemplo de Disparo nos Pontos de Acao

```php
// Dentro de realizarTarefaAction() no CronogramaOperacional.php
// Apos: $tarefa->update(['status' => 'em_analise']);

$diretores = User::whereIn('perfil', ['admin_geral', 'diretor_projetos'])->get();
Notification::send($diretores, new TarefaEnviadaParaAnalise($tarefa, $user));
```

```php
// Dentro de analisarTarefaAction() no CronogramaOperacional.php
// Apos aprovar:

$enviadoPor = $tarefa->realizacoes->last()?->user;
if ($enviadoPor) {
    $enviadoPor->notify(new TarefaAprovada($tarefa, $user));
}

// Apos devolver:
$enviadoPor = $tarefa->realizacoes->last()?->user;
if ($enviadoPor) {
    $enviadoPor->notify(new TarefaDevolvida($tarefa, $user, $data['motivo']));
}
```

---

## Resumo Visual do Fluxo

```
  [Coordenador envia tarefa/prestacao]
            |
            v
    +-------+--------+
    | Status:        |
    | em_analise     |----> Notificacao (sistema + email)
    +-------+--------+      para diretores/admin
            |
    [Diretor/Admin analisa]
            |
      +-----+------+
      |            |
   Aprova       Devolve
      |            |
      v            v
  [realizado]  [devolvido]
      |            |
  Notifica     Notifica (com motivo)
  coordenador  coordenador
  (sistema +   (sistema +
   email)       email)
```

---

## Estimativa de Esforco

| Etapa | Descricao | Complexidade |
|-------|-----------|:------------:|
| 1 | Infraestrutura (migrations, queue, Filament config) | Baixa |
| 2 | 6 classes de Notification + disparo nos controllers | Media |
| 3 | 2 Commands agendados para alertas de prazo | Media |
| 4 | Preferencias do usuario (opcional) | Media |

---

## Dependencias

- **Configuracao de email** (SMTP, Mailgun, SES, etc.) no `.env`
- **Queue worker** rodando em producao (`php artisan queue:work` ou Supervisor)
- **Cron/Scheduler** configurado no servidor para os alertas de prazo
