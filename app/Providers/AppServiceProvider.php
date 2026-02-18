<?php

namespace App\Providers;

use App\Models\Financiador;
use App\Models\Meta;
use App\Models\Polo;
use App\Models\Projeto;
use App\Models\Tarefa;
use App\Models\User;
use App\Models\EtapaPrestacao;
use App\Policies\FinanciadorPolicy;
use App\Policies\MetaPolicy;
use App\Policies\PoloPolicy;
use App\Policies\ProjetoPolicy;
use App\Policies\TarefaPolicy;
use App\Policies\UserPolicy;
use App\Policies\EtapaPrestacaoPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar policies
        Gate::policy(Projeto::class, ProjetoPolicy::class);
        Gate::policy(Meta::class, MetaPolicy::class);
        Gate::policy(Tarefa::class, TarefaPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Polo::class, PoloPolicy::class);
        Gate::policy(Financiador::class, FinanciadorPolicy::class);
        Gate::policy(EtapaPrestacao::class, EtapaPrestacaoPolicy::class);

        // Super Admin e Admin Geral têm acesso total
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdminGeral()) {
                return true;
            }
        });
    }
}
