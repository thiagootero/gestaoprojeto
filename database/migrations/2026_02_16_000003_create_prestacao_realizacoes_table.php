<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestacao_realizacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etapa_prestacao_id')->constrained('etapas_prestacao')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comentario');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestacao_realizacoes');
    }
};
