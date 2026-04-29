<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_gestor_sees_manager_dashboard()
    {
        $gestor = User::factory()->create(['role' => UserRole::Gestor]);

        $response = $this->actingAs($gestor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.gestor');
        $response->assertSee('Painel do Gestor');
    }

    public function test_employee_sees_employee_dashboard()
    {
        $employee = User::factory()->create(['role' => UserRole::Employee]);

        Employee::create([
            'user_id'  => $employee->id,
            'cpf'      => '999.999.999-99',
            'position' => 'Dev',
            'address'  => 'Test Address',
            'hired_at' => now()->toDateString(),
        ]);

        $response = $this->actingAs($employee)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.employee');
        $response->assertSee('Horário Atual');
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }
}
