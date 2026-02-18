<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['role_id', 'user_id']);
        });

        DB::table('roles')->insert([
            ['name' => 'Administrador Geral', 'slug' => 'admin_geral', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Diretor de Projeto', 'slug' => 'diretor_projeto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coordenador de Polo', 'slug' => 'coordenador_polo', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Coordenador Financeiro', 'slug' => 'coordenador_financeiro', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
