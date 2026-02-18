<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financiadores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->enum('tipo', ['publico', 'privado', 'internacional', 'misto']);
            $table->string('contato_nome', 100)->nullable();
            $table->string('contato_email', 100)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financiadores');
    }
};
