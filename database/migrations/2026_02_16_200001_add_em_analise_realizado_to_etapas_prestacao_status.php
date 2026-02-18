<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE etapas_prestacao MODIFY COLUMN status ENUM('pendente','em_elaboracao','enviada','aprovada','com_ressalvas','rejeitada','em_analise','realizado') DEFAULT 'pendente'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE etapas_prestacao MODIFY COLUMN status ENUM('pendente','em_elaboracao','enviada','aprovada','com_ressalvas','rejeitada') DEFAULT 'pendente'");
    }
};
