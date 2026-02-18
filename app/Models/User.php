<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'cargo',
        'ativo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ativo' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->ativo;
    }

    public function polos(): BelongsToMany
    {
        return $this->belongsToMany(Polo::class, 'polo_user')
            ->withTimestamps();
    }

    public function getPoloIdsAttribute(): array
    {
        return $this->polos->pluck('id')->toArray();
    }

    public function tarefasValidadas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'validado_por');
    }

    public function historicoTarefas(): HasMany
    {
        return $this->hasMany(HistoricoTarefa::class);
    }

    public function tarefasResponsavel(): BelongsToMany
    {
        return $this->belongsToMany(Tarefa::class, 'tarefa_user')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->perfil === 'super_admin';
    }

    public function isAdminGeral(): bool
    {
        return $this->perfil === 'admin_geral' || $this->isSuperAdmin();
    }

    public function isDiretorProjeto(): bool
    {
        return $this->perfil === 'diretor_projetos' || $this->isSuperAdmin();
    }

    public function isDiretorProjetos(): bool
    {
        return $this->perfil === 'diretor_projetos' || $this->isSuperAdmin();
    }

    public function isDiretorOperacoes(): bool
    {
        return $this->perfil === 'diretor_operacoes';
    }

    public function isCoordenadorPolo(): bool
    {
        return $this->perfil === 'coordenador_polo';
    }

    public function isCoordenadorFinanceiro(): bool
    {
        return $this->perfil === 'coordenador_financeiro';
    }

    public function getPerfilLabelAttribute(): string
    {
        return match($this->perfil) {
            'super_admin' => 'Super Admin',
            'admin_geral' => 'Administrador Geral',
            'diretor_projetos' => 'Diretor de Projetos',
            'diretor_operacoes' => 'Diretor de Operações',
            'coordenador_polo' => 'Coordenador de Polo',
            'coordenador_financeiro' => 'Coordenador Financeiro',
            default => $this->perfil,
        };
    }
}
