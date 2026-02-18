<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY perfil VARCHAR(50) NOT NULL DEFAULT 'coordenador_polo'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY perfil ENUM('super_admin','diretor_projetos','diretor_operacoes','coordenador_polo') NOT NULL DEFAULT 'coordenador_polo'");
    }
};
