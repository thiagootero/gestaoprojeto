<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etapas_prestacao', function (Blueprint $table) {
            $table->enum('origem', ['financiador', 'interna'])
                ->default('financiador')
                ->after('descricao');
            $table->foreignId('projeto_id')
                ->nullable()
                ->after('origem')
                ->constrained()
                ->nullOnDelete();
            $table->foreignId('projeto_financiador_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('etapas_prestacao', function (Blueprint $table) {
            $table->foreignId('projeto_financiador_id')
                ->nullable(false)
                ->change();
            $table->dropConstrainedForeignId('projeto_id');
            $table->dropColumn('origem');
        });
    }
};
