<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PunchAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestors_cannot_punch_the_clock()
    {
        $gestor = User::factory()->create(['role' => UserRole::Gestor]);

        $response = $this->actingAs($gestor)->postJson(route('punch'));

        $response->assertStatus(403);
    }

    public function test_employee_can_punch_successfully()
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::create([
            'user_id'  => $user->id,
            'cpf'      => '000.000.000-00',
            'position' => 'Tester',
            'address'  => 'Test Address',
            'hired_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->postJson(route('punch'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_employee_has_rate_limiting_in_punch()
    {
        $user = User::factory()->create(['role' => UserRole::Employee]);
        Employee::create([
            'user_id'  => $user->id,
            'cpf'      => '000.000.000-00',
            'position' => 'Tester',
            'address'  => 'Test Address',
            'hired_at' => now()->toDateString(),
        ]);

        $route = route('punch');

        // Normal punch
        $response1 = $this->actingAs($user)->postJson($route);
        $response1->assertStatus(200);

        // Fire excessively to trigger throttle
        for ($i = 0; $i < 6; $i++) {
            $response = $this->actingAs($user)->postJson($route);
        }

        // After 5+ requests, should return 429 Too Many Requests
        $response->assertStatus(429);
    }
}
