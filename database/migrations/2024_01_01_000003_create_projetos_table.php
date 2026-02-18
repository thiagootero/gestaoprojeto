<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projetos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->date('data_encerramento');
            $table->date('encerramento_contratos')->nullable();
            $table->enum('status', [
                'planejamento',
                'em_execucao',
                'suspenso',
                'encerrado',
                'prestacao_final'
            ])->default('planejamento');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projetos');
    }
};
