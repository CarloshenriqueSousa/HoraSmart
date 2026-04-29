<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;
    private User $employeeUser;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gestor = User::factory()->create(['role' => UserRole::Gestor]);

        $this->employeeUser = User::factory()->create(['role' => UserRole::Employee]);
        $this->employee = Employee::create([
            'user_id'  => $this->employeeUser->id,
            'cpf'      => '529.982.247-25',
            'position' => 'Dev',
            'address'  => 'Rua Test, 123',
            'hired_at' => now()->subMonth()->toDateString(),
        ]);
    }

    // ─── Index ──────────────────────────────────

    public function test_gestor_can_list_employees()
    {
        $response = $this->actingAs($this->gestor)->get(route('employees.index'));
        $response->assertStatus(200);
        $response->assertViewIs('employees.index');
    }

    public function test_employee_cannot_list_employees()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employees.index'));
        $response->assertStatus(403);
    }

    // ─── Create ─────────────────────────────────

    public function test_gestor_can_view_create_form()
    {
        $response = $this->actingAs($this->gestor)->get(route('employees.create'));
        $response->assertStatus(200);
    }

    // ─── Store ──────────────────────────────────

    public function test_gestor_can_store_employee()
    {
        $response = $this->actingAs($this->gestor)->post(route('employees.store'), [
            'name'                  => 'Novo Funcionário',
            'email'                 => 'novo@smart.com',
            'cpf'                   => '987.654.321-00',
            'address'               => 'Rua Nova, 456',
            'position'              => 'Analista',
            'employee_type'         => 'CLT',
            'shift'                 => 'morning',
            'daily_workload'        => 480,
            'overtime_tolerance'    => 10,
            'hired_at'              => now()->subWeek()->format('Y-m-d'),
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('users', ['email' => 'novo@smart.com']);
        $this->assertDatabaseHas('employees', ['position' => 'Analista']);
    }

    public function test_store_validates_unique_email()
    {
        $response = $this->actingAs($this->gestor)->post(route('employees.store'), [
            'name'                  => 'Duplicado',
            'email'                 => $this->employeeUser->email, // duplicate
            'cpf'                   => '123.456.789-09',
            'address'               => 'Test',
            'position'              => 'Test',
            'employee_type'         => 'CLT',
            'shift'                 => 'morning',
            'daily_workload'        => 480,
            'overtime_tolerance'    => 10,
            'hired_at'              => now()->format('Y-m-d'),
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_store_validates_unique_cpf()
    {
        $response = $this->actingAs($this->gestor)->post(route('employees.store'), [
            'name'                  => 'Duplicado CPF',
            'email'                 => 'unique@smart.com',
            'cpf'                   => $this->employee->cpf, // duplicate
            'address'               => 'Test',
            'position'              => 'Test',
            'employee_type'         => 'CLT',
            'shift'                 => 'morning',
            'daily_workload'        => 480,
            'overtime_tolerance'    => 10,
            'hired_at'              => now()->format('Y-m-d'),
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('cpf');
    }

    // ─── Show ───────────────────────────────────

    public function test_gestor_can_view_employee()
    {
        $response = $this->actingAs($this->gestor)->get(route('employees.show', $this->employee));
        $response->assertStatus(200);
        $response->assertViewIs('employees.show');
    }

    // ─── Update ─────────────────────────────────

    public function test_gestor_can_update_employee()
    {
        $response = $this->actingAs($this->gestor)->put(route('employees.update', $this->employee), [
            'name'               => 'Nome Atualizado',
            'email'              => $this->employeeUser->email,
            'cpf'                => $this->employee->cpf,
            'address'            => 'Rua Atualizada, 789',
            'position'           => 'Senior Dev',
            'employee_type'      => 'CLT',
            'shift'              => 'afternoon',
            'daily_workload'     => 480,
            'overtime_tolerance' => 5,
            'hired_at'           => $this->employee->hired_at->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('employees.index'));
        $this->assertDatabaseHas('users', ['name' => 'Nome Atualizado']);
        $this->assertDatabaseHas('employees', ['position' => 'Senior Dev']);
    }

    // ─── Destroy ────────────────────────────────

    public function test_gestor_can_delete_employee()
    {
        $response = $this->actingAs($this->gestor)->delete(route('employees.destroy', $this->employee));
        $response->assertRedirect(route('employees.index'));
    }

    public function test_employee_cannot_delete_other_employee()
    {
        $response = $this->actingAs($this->employeeUser)->delete(route('employees.destroy', $this->employee));
        $response->assertStatus(403);
    }

    // ─── Export ──────────────────────────────────

    public function test_gestor_can_export_csv()
    {
        $response = $this->actingAs($this->gestor)->get(route('employees.export.csv'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_cannot_export_csv()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('employees.export.csv'));
        $response->assertStatus(403);
    }
}
