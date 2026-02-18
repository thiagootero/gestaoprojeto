<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjetoFinanciador extends Model
{
    protected $table = 'projeto_financiador';

    protected $fillable = [
        'projeto_id',
        'financiador_id',
        'data_inicio_contrato',
        'data_fim_contrato',
        'data_prorrogacao',
        'prorrogado_ate',
        'pc_interna_dia',
        'pc_interna_dia_fin',
        'periodicidade',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio_contrato' => 'date',
            'data_fim_contrato' => 'date',
            'data_prorrogacao' => 'date',
            'prorrogado_ate' => 'date',
        ];
    }

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class);
    }

    public function financiador(): BelongsTo
    {
        return $this->belongsTo(Financiador::class);
    }

    public function etapasPrestacao(): HasMany
    {
        return $this->hasMany(EtapaPrestacao::class)
            ->orderBy('tipo')
            ->orderBy('numero_etapa');
    }

    public function gerarEtapas(): void
    {
        if (!$this->periodicidade || !$this->data_inicio_contrato || !$this->data_fim_contrato) {
            return;
        }

        $meses = match($this->periodicidade) {
            'mensal' => 1,
            'bimestral' => 2,
            'trimestral' => 3,
            'semestral' => 6,
            'anual' => 12,
            default => null,
        };

        if (!$meses) {
            return;
        }

        $inicio = Carbon::parse($this->data_inicio_contrato);
        $fim = Carbon::parse($this->data_fim_contrato);
        $etapa = 1;
        $periodoInicio = $inicio->copy();

        while ($periodoInicio->lt($fim)) {
            $periodoFim = $periodoInicio->copy()->addMonths($meses)->subDay();

            if ($periodoFim->gt($fim)) {
                $periodoFim = $fim->copy();
            }

            foreach (['qualitativa', 'financeira'] as $tipo) {
                EtapaPrestacao::create([
                    'origem' => 'financiador',
                    'projeto_id' => $this->projeto_id,
                    'projeto_financiador_id' => $this->id,
                    'numero_etapa' => $etapa,
                    'tipo' => $tipo,
                    'data_limite' => $periodoFim->copy()->addDays(30),
                    'periodo_inicio' => $periodoInicio,
                    'periodo_fim' => $periodoFim,
                ]);
            }

            $periodoInicio = $periodoFim->copy()->addDay();
            $etapa++;
        }
    }

    public function getPeriodicidadeLabelAttribute(): ?string
    {
        if (!$this->periodicidade) {
            return 'Manual';
        }

        return match($this->periodicidade) {
            'mensal' => 'Mensal',
            'bimestral' => 'Bimestral',
            'trimestral' => 'Trimestral',
            'semestral' => 'Semestral',
            'anual' => 'Anual',
            default => $this->periodicidade,
        };
    }
}
