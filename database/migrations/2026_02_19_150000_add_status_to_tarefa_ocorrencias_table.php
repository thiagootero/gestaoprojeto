<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefa_ocorrencias', function (Blueprint $table) {
            $table->string('status', 20)->default('pendente')->after('data_fim');
            $table->boolean('comprovacao_validada')->default(false)->after('status');
            $table->unsignedBigInteger('validado_por')->nullable()->after('comprovacao_validada');
            $table->dateTime('validado_em')->nullable()->after('validado_por');
            $table->text('observacoes')->nullable()->after('validado_em');
        });
    }

    public function down(): void
    {
        Schema::table('tarefa_ocorrencias', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'comprovacao_validada',
                'validado_por',
                'validado_em',
                'observacoes',
            ]);
        });
    }
};
