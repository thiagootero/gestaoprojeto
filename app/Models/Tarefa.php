<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarefa extends Model
{
    protected $fillable = [
        'meta_id',
        'numero',
        'descricao',
        'responsavel',
        'responsavel_user_id',
        'polo_id',
        'data_inicio',
        'data_fim',
        'status',
        'como_fazer',
        'link_comprovacao',
        'comprovacao_validada',
        'validado_por',
        'validado_em',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_fim' => 'date',
            'comprovacao_validada' => 'boolean',
            'validado_em' => 'datetime',
        ];
    }

    public function meta(): BelongsTo
    {
        return $this->belongsTo(Meta::class);
    }

    public function polo(): BelongsTo
    {
        return $this->belongsTo(Polo::class);
    }

    public function responsavelUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_user_id');
    }

    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tarefa_user')
            ->withTimestamps();
    }

    public function validadoPorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function historicoTarefas(): HasMany
    {
        return $this->hasMany(HistoricoTarefa::class)->orderByDesc('created_at');
    }

    public function ocorrencias(): HasMany
    {
        return $this->hasMany(TarefaOcorrencia::class)->orderBy('data_fim');
    }

    public function realizacoes(): HasMany
    {
        return $this->hasMany(TarefaRealizacao::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->getStatusNormalizado()) {
            'pendente' => 'Pendente',
            'em_execucao' => 'Em Execução',
            'em_analise' => 'Em Análise',
            'devolvido' => 'Devolvido para ajuste',
            'com_ressalvas' => 'Validado com ressalva',
            'realizado' => 'Realizado',
            default => 'Pendente',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->getStatusNormalizado()) {
            'pendente' => 'danger',
            'em_execucao' => 'info',
            'em_analise' => 'warning',
            'devolvido' => 'danger',
            'com_ressalvas' => 'success',
            'realizado' => 'success',
            default => 'gray',
        };
    }

    public function getAtrasadaAttribute(): bool
    {
        if (!$this->data_fim) {
            return false;
        }
        return $this->data_fim->isPast() && !in_array($this->status, ['realizado', 'concluido', 'com_ressalvas']);
    }

    public function getVencendoAttribute(): bool
    {
        if (!$this->data_fim) {
            return false;
        }
        $diasRestantes = now()->startOfDay()->diffInDays($this->data_fim, false);
        return $diasRestantes >= 0 && $diasRestantes <= 7 && !in_array($this->status, ['realizado', 'concluido', 'com_ressalvas']);
    }

    public function getStatusNormalizado(): string
    {
        return match($this->status) {
            'pendente', 'a_iniciar', 'nao_realizado', 'cancelado' => 'pendente',
            'em_execucao', 'em_andamento' => 'em_execucao',
            'em_analise' => 'em_analise',
            'devolvido' => 'devolvido',
            'com_ressalvas' => 'com_ressalvas',
            'realizado', 'concluido' => 'realizado',
            default => 'pendente',
        };
    }
}
