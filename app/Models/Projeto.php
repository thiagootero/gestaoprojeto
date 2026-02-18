<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projeto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nome',
        'descricao',
        'data_inicio',
        'data_encerramento',
        'encerramento_contratos',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_encerramento' => 'date',
            'encerramento_contratos' => 'date',
        ];
    }

    public function polos(): BelongsToMany
    {
        return $this->belongsToMany(Polo::class, 'projeto_polo')
            ->withTimestamps();
    }

    public function financiadores(): BelongsToMany
    {
        return $this->belongsToMany(Financiador::class, 'projeto_financiador')
            ->withTimestamps();
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(ProjetoFinanciador::class);
    }

    public function metas(): HasMany
    {
        return $this->hasMany(Meta::class)->orderBy('numero');
    }

    public function etapasPrestacao(): HasMany
    {
        return $this->hasMany(EtapaPrestacao::class);
    }

    public function tarefas(): HasManyThrough
    {
        return $this->hasManyThrough(Tarefa::class, Meta::class);
    }

    public function getPercentualConclusaoAttribute(): float
    {
        $total = $this->tarefas()->count();
        if ($total === 0) {
            return 0;
        }
        $concluidas = $this->tarefas()->whereIn('tarefas.status', ['realizado', 'concluido'])->count();
        return round(($concluidas / $total) * 100, 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'planejamento' => 'Planejamento',
            'em_execucao' => 'Em Execução',
            'suspenso' => 'Suspenso',
            'encerrado' => 'Encerrado',
            'prestacao_final' => 'Prestação Final',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'planejamento' => 'gray',
            'em_execucao' => 'success',
            'suspenso' => 'warning',
            'encerrado' => 'info',
            'prestacao_final' => 'primary',
            default => 'gray',
        };
    }
}
