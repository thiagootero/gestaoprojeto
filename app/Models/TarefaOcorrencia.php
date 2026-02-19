<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarefaOcorrencia extends Model
{
    protected $table = 'tarefa_ocorrencias';

    protected $fillable = [
        'tarefa_id',
        'data_fim',
        'status',
        'comprovacao_validada',
        'validado_por',
        'validado_em',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_fim' => 'date',
            'comprovacao_validada' => 'boolean',
            'validado_em' => 'datetime',
        ];
    }

    public function validadoPorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
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

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }
}
