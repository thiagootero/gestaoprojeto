<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Criar tabela pivot polo_user
        Schema::create('polo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('polo_id')->constrained('polos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['polo_id', 'user_id']);
        });

        // 2. Migrar dados: users com polo_id vão para a pivot
        $usersComPolo = DB::table('users')->whereNotNull('polo_id')->get(['id', 'polo_id']);
        foreach ($usersComPolo as $user) {
            DB::table('polo_user')->insert([
                'polo_id' => $user->polo_id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Migrar coordenador_id dos polos para a pivot (se ainda não estiver)
        $polosComCoord = DB::table('polos')->whereNotNull('coordenador_id')->get(['id', 'coordenador_id']);
        foreach ($polosComCoord as $polo) {
            DB::table('polo_user')->insertOrIgnore([
                'polo_id' => $polo->id,
                'user_id' => $polo->coordenador_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Remover colunas antigas
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('polo_id');
        });

        Schema::table('polos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coordenador_id');
            $table->dropColumn('cidade');
        });
    }

    public function down(): void
    {
        Schema::table('polos', function (Blueprint $table) {
            $table->string('cidade', 100)->nullable()->after('nome');
            $table->foreignId('coordenador_id')->nullable()
                ->constrained('users')->nullOnDelete()->after('cidade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('polo_id')->nullable()
                ->constrained()->nullOnDelete()->after('perfil');
        });

        // Migrar de volta da pivot para polo_id
        $pivotData = DB::table('polo_user')->get();
        foreach ($pivotData as $row) {
            DB::table('users')
                ->where('id', $row->user_id)
                ->whereNull('polo_id')
                ->update(['polo_id' => $row->polo_id]);
        }

        Schema::dropIfExists('polo_user');
    }
};
