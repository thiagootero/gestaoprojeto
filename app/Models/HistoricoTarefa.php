<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoTarefa extends Model
{
    public $timestamps = false;

    protected $table = 'historico_tarefas';

    protected $fillable = [
        'tarefa_id',
        'user_id',
        'status_anterior',
        'status_novo',
        'observacao',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function tarefa(): BelongsTo
    {
        return $this->belongsTo(Tarefa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
