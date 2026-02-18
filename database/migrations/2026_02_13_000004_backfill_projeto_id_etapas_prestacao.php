<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE etapas_prestacao ep
            JOIN projeto_financiador pf ON pf.id = ep.projeto_financiador_id
            SET ep.projeto_id = pf.projeto_id
            WHERE ep.projeto_id IS NULL
        ");
    }

    public function down(): void
    {
        // No-op: keep projeto_id as-is
    }
};
