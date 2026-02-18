<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etapas_prestacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_financiador_id')
                ->constrained('projeto_financiador')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('numero_etapa');
            $table->enum('tipo', ['qualitativa', 'financeira']);
            $table->date('data_limite');
            $table->date('periodo_inicio')->nullable();
            $table->date('periodo_fim')->nullable();
            $table->enum('status', [
                'pendente',
                'em_elaboracao',
                'enviada',
                'aprovada',
                'com_ressalvas',
                'rejeitada'
            ])->default('pendente');
            $table->date('data_envio')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['projeto_financiador_id', 'numero_etapa', 'tipo'], 'etapa_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas_prestacao');
    }
};
