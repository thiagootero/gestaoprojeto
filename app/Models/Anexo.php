<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Anexo extends Model
{
    protected $fillable = [
        'tipo',
        'descricao',
        'arquivo',
    ];

    public function projetos(): BelongsToMany
    {
        return $this->belongsToMany(Projeto::class, 'anexo_projeto')
            ->withTimestamps();
    }
}
