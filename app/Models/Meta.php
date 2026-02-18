<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meta extends Model
{
    protected $fillable = [
        'projeto_id',
        'numero',
        'descricao',
        'indicador',
        'meio_verificacao',
        'status',
    ];

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class)->orderByRaw("CAST(SUBSTRING_INDEX(numero, '.', -1) AS UNSIGNED)");
    }

    public function getPercentualConclusaoAttribute(): float
    {
        $total = $this->tarefas()->count();
        if ($total === 0) {
            return 0;
        }
        $concluidas = $this->tarefas()->whereIn('status', ['realizado', 'concluido'])->count();
        return round(($concluidas / $total) * 100, 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->getStatusNormalizado()) {
            'pendente' => 'Pendente',
            'em_execucao' => 'Em Execução',
            'realizado' => 'Realizado',
            default => 'Pendente',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->getStatusNormalizado()) {
            'pendente' => 'danger',
            'em_execucao' => 'info',
            'realizado' => 'success',
            default => 'gray',
        };
    }

    public function getStatusNormalizado(): string
    {
        return match($this->status) {
            'pendente', 'a_iniciar', 'nao_alcancada' => 'pendente',
            'em_execucao', 'em_andamento', 'parcialmente_alcancada' => 'em_execucao',
            'realizado', 'alcancada' => 'realizado',
            default => 'pendente',
        };
    }
}
