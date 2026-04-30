<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historico_tarefas', function (Blueprint $table) {
            $table->foreignId('tarefa_ocorrencia_id')
                ->nullable()
                ->after('tarefa_id')
                ->constrained('tarefa_ocorrencias')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historico_tarefas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tarefa_ocorrencia_id');
        });
    }
};
