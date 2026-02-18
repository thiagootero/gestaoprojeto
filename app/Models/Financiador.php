<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Financiador extends Model
{
    protected $table = 'financiadores';

    protected $fillable = [
        'nome',
        'tipo',
        'contato_nome',
        'contato_email',
        'observacoes',
    ];

    public function projetoFinanciadores(): HasMany
    {
        return $this->hasMany(ProjetoFinanciador::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'publico' => 'Público',
            'privado' => 'Privado',
            'internacional' => 'Internacional',
            'misto' => 'Misto',
            default => $this->tipo,
        };
    }
}
