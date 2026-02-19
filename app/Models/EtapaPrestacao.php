<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EtapaPrestacao extends Model
{
    protected $table = 'etapas_prestacao';

    protected $fillable = [
        'origem',
        'projeto_id',
        'prestacao_grupo_id',
        'recorrencia_tipo',
        'recorrencia_inicio',
        'recorrencia_dia',
        'recorrencia_repeticoes',
        'ate_final_projeto',
        'datas_personalizadas',
        'projeto_financiador_id',
        'numero_etapa',
        'descricao',
        'tipo',
        'data_limite',
        'periodo_inicio',
        'periodo_fim',
        'status',
        'data_envio',
        'observacoes',
        'validado_por',
        'validado_em',
    ];

    protected function casts(): array
    {
        return [
            'data_limite' => 'date',
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'data_envio' => 'date',
            'validado_em' => 'datetime',
            'recorrencia_inicio' => 'date',
            'ate_final_projeto' => 'boolean',
            'datas_personalizadas' => 'array',
        ];
    }

    public function projetoFinanciador(): BelongsTo
    {
        return $this->belongsTo(ProjetoFinanciador::class);
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function realizacoes(): HasMany
    {
        return $this->hasMany(PrestacaoRealizacao::class, 'etapa_prestacao_id');
    }

    public function validadoPorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'qualitativa' => 'Qualitativa',
            'financeira' => 'Financeira',
            default => $this->tipo,
        };
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
            'pendente', 'rejeitada' => 'pendente',
            'em_execucao', 'em_elaboracao', 'enviada' => 'em_execucao',
            'em_analise' => 'em_analise',
            'devolvido' => 'devolvido',
            'com_ressalvas' => 'com_ressalvas',
            'realizado', 'aprovada' => 'realizado',
            default => 'pendente',
        };
    }

    public function getDiasRestantesAttribute(): int
    {
        return now()->startOfDay()->diffInDays($this->data_limite, false);
    }

    public function getUrgenciaColorAttribute(): string
    {
        $dias = $this->dias_restantes;

        if ($dias < 0) {
            return 'danger'; // Atrasado
        }
        if ($dias <= 15) {
            return 'danger'; // Urgente
        }
        if ($dias <= 30) {
            return 'warning'; // Atenção
        }
        return 'success'; // OK
    }
}
