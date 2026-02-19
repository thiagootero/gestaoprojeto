<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefa_realizacoes', function (Blueprint $table) {
            $table->unsignedBigInteger('tarefa_ocorrencia_id')->nullable()->after('tarefa_id');
            $table->index('tarefa_ocorrencia_id');
        });
    }

    public function down(): void
    {
        Schema::table('tarefa_realizacoes', function (Blueprint $table) {
            $table->dropIndex(['tarefa_ocorrencia_id']);
            $table->dropColumn('tarefa_ocorrencia_id');
        });
    }
};
