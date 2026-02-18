<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projeto_financiador', function (Blueprint $table) {
            $table->dropColumn('valor_aprovado');
        });
    }

    public function down(): void
    {
        Schema::table('projeto_financiador', function (Blueprint $table) {
            $table->decimal('valor_aprovado', 12, 2)->nullable()->after('financiador_id');
        });
    }
};
