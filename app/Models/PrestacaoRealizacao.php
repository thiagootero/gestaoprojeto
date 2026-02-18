<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrestacaoRealizacao extends Model
{
    protected $table = 'prestacao_realizacoes';

    protected $fillable = [
        'etapa_prestacao_id',
        'user_id',
        'comentario',
    ];

    public function etapa(): BelongsTo
    {
        return $this->belongsTo(EtapaPrestacao::class, 'etapa_prestacao_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
