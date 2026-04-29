<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\WorkLogStatus;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkLogControllerTest extends TestCase
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
            'address'  => 'Test',
            'hired_at' => now()->subMonth()->toDateString(),
        ]);

        // Criar alguns worklogs
        for ($i = 1; $i <= 5; $i++) {
            WorkLog::create([
                'employee_id'    => $this->employee->id,
                'work_date'      => today()->subDays($i),
                'clock_in'       => today()->subDays($i)->setTime(8, 0),
                'lunch_out'      => today()->subDays($i)->setTime(12, 0),
                'lunch_in'       => today()->subDays($i)->setTime(13, 0),
                'clock_out'      => today()->subDays($i)->setTime(17, 0),
                'minutes_worked' => 480,
                'status'         => WorkLogStatus::Complete,
            ]);
        }
    }

    // ─── Index ──────────────────────────────────

    public function test_gestor_can_list_all_worklogs()
    {
        $response = $this->actingAs($this->gestor)->get(route('worklogs.index'));
        $response->assertStatus(200);
        $response->assertViewIs('workslogs.index');
    }

    public function test_employee_can_list_own_worklogs()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.index'));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_see_others_worklogs()
    {
        $otherUser = User::factory()->create(['role' => UserRole::Employee]);
        $otherEmployee = Employee::create([
            'user_id'  => $otherUser->id,
            'cpf'      => '111.222.333-96',
            'position' => 'Other',
            'address'  => 'Other',
            'hired_at' => now()->toDateString(),
        ]);

        $otherLog = WorkLog::create([
            'employee_id'    => $otherEmployee->id,
            'work_date'      => today(),
            'clock_in'       => today()->setTime(8, 0),
            'status'         => WorkLogStatus::InProgress,
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.show', $otherLog));
        $response->assertStatus(403);
    }

    // ─── Filtro por mês ─────────────────────────

    public function test_worklogs_filterable_by_month()
    {
        $month = today()->subDays(2)->format('Y-m');

        $response = $this->actingAs($this->gestor)->get(route('worklogs.index', ['month' => $month]));
        $response->assertStatus(200);
    }

    // ─── Show ───────────────────────────────────

    public function test_gestor_can_view_any_worklog()
    {
        $log = WorkLog::first();
        $response = $this->actingAs($this->gestor)->get(route('worklogs.show', $log));
        $response->assertStatus(200);
    }

    public function test_employee_can_view_own_worklog()
    {
        $log = WorkLog::first();
        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.show', $log));
        $response->assertStatus(200);
    }

    // ─── Edit/Update ────────────────────────────

    public function test_gestor_can_edit_worklog()
    {
        $log = WorkLog::first();
        $response = $this->actingAs($this->gestor)->get(route('worklogs.edit', $log));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_edit_worklog()
    {
        $log = WorkLog::first();
        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.edit', $log));
        $response->assertStatus(403);
    }

    public function test_gestor_can_update_worklog()
    {
        $log = WorkLog::first();
        $response = $this->actingAs($this->gestor)->put(route('worklogs.update', $log), [
            'clock_in'  => $log->work_date->format('Y-m-d') . ' 07:30:00',
            'lunch_out' => $log->work_date->format('Y-m-d') . ' 12:00:00',
            'lunch_in'  => $log->work_date->format('Y-m-d') . ' 13:00:00',
            'clock_out' => $log->work_date->format('Y-m-d') . ' 17:30:00',
        ]);

        $response->assertRedirect(route('worklogs.show', $log));

        $log->refresh();
        $this->assertEquals('07:30:00', $log->clock_in->format('H:i:s'));
        // Should be recalculated: 4.5h + 4.5h = 9h = 540 min
        $this->assertEquals(540, $log->minutes_worked);
    }

    // ─── Export CSV ──────────────────────────────

    public function test_gestor_can_export_csv()
    {
        $response = $this->actingAs($this->gestor)->get(route('worklogs.export.csv'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_employee_cannot_export_csv()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.export.csv'));
        $response->assertStatus(403);
    }

    // ─── Export PDF ──────────────────────────────

    public function test_gestor_can_export_pdf()
    {
        $month = today()->format('Y-m');
        $response = $this->actingAs($this->gestor)->get(route('worklogs.export.pdf', ['month' => $month]));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_export_pdf()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('worklogs.export.pdf'));
        $response->assertStatus(403);
    }
}
