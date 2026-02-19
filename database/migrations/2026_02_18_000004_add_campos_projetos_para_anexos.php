<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->string('descricao_breve', 255)->nullable()->after('descricao');
            $table->date('data_limite_prorrogacao_contrato')->nullable()->after('encerramento_contratos');
            $table->date('data_aditivo')->nullable()->after('data_limite_prorrogacao_contrato');
        });
    }

    public function down(): void
    {
        Schema::table('projetos', function (Blueprint $table) {
            $table->dropColumn([
                'descricao_breve',
                'data_limite_prorrogacao_contrato',
                'data_aditivo',
            ]);
        });
    }
};
