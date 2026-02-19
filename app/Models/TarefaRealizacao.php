<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarefaRealizacao extends Model
{
    protected $table = 'tarefa_realizacoes';

    protected $fillable = [
        'tarefa_id',
        'tarefa_ocorrencia_id',
        'user_id',
        'comentario',
    ];

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function ocorrencia(): BelongsTo
    {
        return $this->belongsTo(TarefaOcorrencia::class, 'tarefa_ocorrencia_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
