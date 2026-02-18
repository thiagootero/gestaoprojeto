<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrar dados: copiar roles para o campo perfil quando o perfil ainda não reflete a role
        // admin_geral: users com role admin_geral que não têm perfil admin_geral
        $adminGeralRoleId = DB::table('roles')->where('slug', 'admin_geral')->value('id');
        if ($adminGeralRoleId) {
            $userIds = DB::table('role_user')->where('role_id', $adminGeralRoleId)->pluck('user_id');
            DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotIn('perfil', ['super_admin', 'admin_geral'])
                ->update(['perfil' => 'admin_geral']);
        }

        // coordenador_financeiro: users com role coordenador_financeiro
        $coordFinRoleId = DB::table('roles')->where('slug', 'coordenador_financeiro')->value('id');
        if ($coordFinRoleId) {
            $userIds = DB::table('role_user')->where('role_id', $coordFinRoleId)->pluck('user_id');
            DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotIn('perfil', ['super_admin', 'admin_geral', 'coordenador_financeiro'])
                ->update(['perfil' => 'coordenador_financeiro']);
        }

        // coordenador_polo: users com role coordenador_polo que não têm perfil coordenador_polo
        $coordPoloRoleId = DB::table('roles')->where('slug', 'coordenador_polo')->value('id');
        if ($coordPoloRoleId) {
            $userIds = DB::table('role_user')->where('role_id', $coordPoloRoleId)->pluck('user_id');
            DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotIn('perfil', ['super_admin', 'admin_geral', 'diretor_projetos', 'diretor_operacoes', 'coordenador_polo'])
                ->update(['perfil' => 'coordenador_polo']);
        }

        // diretor_projeto -> diretor_projetos
        $dirProjetoRoleId = DB::table('roles')->where('slug', 'diretor_projeto')->value('id');
        if ($dirProjetoRoleId) {
            $userIds = DB::table('role_user')->where('role_id', $dirProjetoRoleId)->pluck('user_id');
            DB::table('users')
                ->whereIn('id', $userIds)
                ->whereNotIn('perfil', ['super_admin', 'admin_geral', 'diretor_projetos'])
                ->update(['perfil' => 'diretor_projetos']);
        }

        // 2. Remover tabelas de roles
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        // Recriar tabelas de roles
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
};
