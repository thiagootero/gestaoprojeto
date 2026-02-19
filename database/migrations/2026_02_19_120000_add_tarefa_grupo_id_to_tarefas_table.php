<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->string('tarefa_grupo_id', 36)->nullable()->index()->after('meta_id');
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropIndex(['tarefa_grupo_id']);
            $table->dropColumn('tarefa_grupo_id');
        });
    }
};
