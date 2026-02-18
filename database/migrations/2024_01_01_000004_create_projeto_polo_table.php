<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projeto_polo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projeto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('polo_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['projeto_id', 'polo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projeto_polo');
    }
};
