<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Polo extends Model
{
    protected $fillable = [
        'nome',
        'ativo',
        'is_geral',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'is_geral' => 'boolean',
        ];
    }

    public function coordenadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'polo_user')
            ->withTimestamps();
    }

    public function projetos(): BelongsToMany
    {
        return $this->belongsToMany(Projeto::class, 'projeto_polo')
            ->withTimestamps();
    }

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class);
    }
}
