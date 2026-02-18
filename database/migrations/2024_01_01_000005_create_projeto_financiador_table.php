<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projeto_financiador', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('financiador_id')->constrained('financiadores')->cascadeOnDelete();
            $table->date('data_inicio_contrato')->nullable();
            $table->date('data_fim_contrato')->nullable();
            $table->date('data_prorrogacao')->nullable();
            $table->date('prorrogado_ate')->nullable();
            $table->unsignedTinyInteger('pc_interna_dia')->nullable();
            $table->unsignedTinyInteger('pc_interna_dia_fin')->nullable();
            $table->enum('periodicidade', [
                'mensal',
                'bimestral',
                'trimestral',
                'semestral',
                'anual'
            ])->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['projeto_id', 'financiador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_financiador');
    }
};
