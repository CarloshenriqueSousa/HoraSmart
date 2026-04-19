<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PunchAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestors_cannot_punch_the_clock()
    {
        $gestor = User::factory()->create(['role' => 'gestor']);

        $response = $this->actingAs($gestor)->postJson(route('punch'));

        // Deve receber acesso negado (Forbidden) porque o gestor não deve bater ponto
        $response->assertStatus(403);
    }

    public function test_employee_has_rate_limiting_in_punch()
    {
        $user = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'user_id' => $user->id,
            'cpf' => '00000000000',
            'position' => 'Tester',
            'address' => 'Test Address',
            'hired_at' => now()->toDateString()
        ]);

        $route = route('punch');

        // Bate ponto normal
        $response1 = $this->actingAs($user)->postJson($route);
        $response1->assertStatus(200);

        // Dispara excessivamente para ativar o throttle
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)->postJson($route);
        }

        // Após passar de 5 requests (throttle global de 5,1 no grupo punch), 
        // a próxima deve retornar status 429 Too Many Requests
        $response->assertStatus(429);
    }
}
