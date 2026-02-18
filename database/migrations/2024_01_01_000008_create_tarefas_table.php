<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meta_id')->constrained()->cascadeOnDelete();
            $table->string('numero', 10);
            $table->text('descricao');
            $table->string('responsavel', 200)->nullable();
            $table->foreignId('responsavel_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->foreignId('polo_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->enum('status', [
                'a_iniciar',
                'em_andamento',
                'concluido',
                'nao_realizado',
                'cancelado'
            ])->default('a_iniciar');
            $table->text('como_fazer')->nullable();
            $table->string('link_comprovacao', 500)->nullable();
            $table->boolean('comprovacao_validada')->default(false);
            $table->foreignId('validado_por')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->datetime('validado_em')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
