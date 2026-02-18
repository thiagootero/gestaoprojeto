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
    ];

    protected function casts(): array
    {
        return [
            'data_fim' => 'date',
        ];
    }

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }
}
