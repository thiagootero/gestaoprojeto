<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SsoLoginControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('central_user_id')->nullable()->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('perfil')->default('coordenador_polo');
            $table->string('cargo', 100)->nullable();
            $table->boolean('ativo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function test_it_creates_and_logs_in_user_from_portal(): void
    {
        Http::fake([
            'http://localhost:8001/api/sso/consume' => Http::response([
                'user' => [
                    'central_user_id' => 51,
                    'name' => 'Gestao Portal',
                    'email' => 'gestao@example.com',
                    'is_admin' => true,
                    'is_active' => true,
                ],
            ]),
        ]);

        $response = $this->get('/sso/login?sso_token=abc123');

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'central_user_id' => 51,
            'email' => 'gestao@example.com',
            'perfil' => 'super_admin',
            'ativo' => 1,
        ]);
    }

    public function test_it_updates_existing_user_found_by_email_without_overriding_local_profile(): void
    {
        User::factory()->create([
            'email' => 'gestao@example.com',
            'perfil' => 'diretor_projetos',
            'ativo' => false,
        ]);

        Http::fake([
            'http://localhost:8001/api/sso/consume' => Http::response([
                'user' => [
                    'central_user_id' => 64,
                    'name' => 'Gestao Portal',
                    'email' => 'gestao@example.com',
                    'is_admin' => false,
                    'is_active' => true,
                ],
            ]),
        ]);

        $this->get('/sso/login?sso_token=abc123')
            ->assertRedirect('/admin');

        $this->assertDatabaseHas('users', [
            'central_user_id' => 64,
            'email' => 'gestao@example.com',
            'perfil' => 'diretor_projetos',
            'ativo' => 1,
        ]);
    }
}
