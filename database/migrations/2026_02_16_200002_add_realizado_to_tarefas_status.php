<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE tarefas MODIFY COLUMN status ENUM('a_iniciar','em_andamento','concluido','nao_realizado','cancelado','realizado') DEFAULT 'a_iniciar'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tarefas MODIFY COLUMN status ENUM('a_iniciar','em_andamento','concluido','nao_realizado','cancelado') DEFAULT 'a_iniciar'");
    }
};
