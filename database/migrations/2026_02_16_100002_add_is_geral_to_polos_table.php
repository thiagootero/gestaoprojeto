<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polos', function (Blueprint $table) {
            $table->boolean('is_geral')->default(false)->after('ativo');
        });
    }

    public function down(): void
    {
        Schema::table('polos', function (Blueprint $table) {
            $table->dropColumn('is_geral');
        });
    }
};
