<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('perfil', [
                'super_admin',
                'diretor_projetos',
                'diretor_operacoes',
                'coordenador_polo'
            ])->default('coordenador_polo')->after('password');
            $table->foreignId('polo_id')->nullable()
                ->constrained()->nullOnDelete()->after('perfil');
            $table->string('cargo', 100)->nullable()->after('polo_id');
            $table->boolean('ativo')->default(true)->after('cargo');
        });

        // Adicionar coordenador_id na tabela polos
        Schema::table('polos', function (Blueprint $table) {
            $table->foreignId('coordenador_id')->nullable()
                ->constrained('users')->nullOnDelete()->after('cidade');
        });
    }

    public function down(): void
    {
        Schema::table('polos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coordenador_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('polo_id');
            $table->dropColumn(['perfil', 'cargo', 'ativo']);
        });
    }
};
