<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefa_ocorrencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->date('data_fim');
            $table->timestamps();

            $table->unique(['tarefa_id', 'data_fim']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefa_ocorrencias');
    }
};
