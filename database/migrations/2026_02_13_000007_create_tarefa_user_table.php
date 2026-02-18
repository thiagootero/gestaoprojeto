<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tarefa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefa_user');
    }
};
