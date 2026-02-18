<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarefa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('status_anterior', 30)->nullable();
            $table->string('status_novo', 30);
            $table->text('observacao')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_tarefas');
    }
};
