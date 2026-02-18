<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('numero');
            $table->text('descricao');
            $table->text('indicador')->nullable();
            $table->text('meio_verificacao')->nullable();
            $table->enum('status', [
                'a_iniciar',
                'em_andamento',
                'alcancada',
                'nao_alcancada',
                'parcialmente_alcancada'
            ])->default('a_iniciar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas');
    }
};
