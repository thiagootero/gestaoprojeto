<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->string('recorrencia_tipo', 20)->nullable()->after('tarefa_grupo_id');
            $table->date('recorrencia_inicio')->nullable()->after('recorrencia_tipo');
            $table->unsignedTinyInteger('recorrencia_dia')->nullable()->after('recorrencia_inicio');
            $table->unsignedSmallInteger('recorrencia_repeticoes')->nullable()->after('recorrencia_dia');
            $table->boolean('ate_final_projeto')->default(false)->after('recorrencia_repeticoes');
            $table->json('datas_personalizadas')->nullable()->after('ate_final_projeto');
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropColumn([
                'recorrencia_tipo',
                'recorrencia_inicio',
                'recorrencia_dia',
                'recorrencia_repeticoes',
                'ate_final_projeto',
                'datas_personalizadas',
            ]);
        });
    }
};
