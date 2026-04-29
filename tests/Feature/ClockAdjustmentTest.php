<?php

namespace Tests\Feature;

use App\Enums\AdjustmentStatus;
use App\Enums\UserRole;
use App\Enums\WorkLogStatus;
use App\Models\ClockAdjustment;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    private User $gestor;
    private User $employeeUser;
    private Employee $employee;
    private WorkLog $workLog;

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

        $this->workLog = WorkLog::create([
            'employee_id'    => $this->employee->id,
            'work_date'      => today()->subDay(),
            'clock_in'       => today()->subDay()->setTime(8, 0),
            'lunch_out'      => today()->subDay()->setTime(12, 0),
            'lunch_in'       => today()->subDay()->setTime(13, 0),
            'clock_out'      => today()->subDay()->setTime(17, 0),
            'minutes_worked' => 480,
            'status'         => WorkLogStatus::Complete,
        ]);
    }

    // ─── Store ──────────────────────────────────

    public function test_employee_can_request_adjustment()
    {
        $response = $this->actingAs($this->employeeUser)->post(route('adjustments.store'), [
            'work_log_id'    => $this->workLog->id,
            'field'          => 'clock_in',
            'requested_time' => today()->subDay()->setTime(7, 30)->format('Y-m-d H:i:s'),
            'reason'         => 'Cheguei mais cedo mas esqueci de bater o ponto na hora correta.',
        ]);

        $response->assertRedirect(route('adjustments.index'));
        $this->assertDatabaseHas('clock_adjustments', [
            'work_log_id'  => $this->workLog->id,
            'field'        => 'clock_in',
            'status'       => AdjustmentStatus::Pending->value,
        ]);
    }

    public function test_gestor_cannot_request_adjustment()
    {
        $response = $this->actingAs($this->gestor)->post(route('adjustments.store'), [
            'work_log_id'    => $this->workLog->id,
            'field'          => 'clock_in',
            'requested_time' => today()->subDay()->setTime(7, 30)->format('Y-m-d H:i:s'),
            'reason'         => 'Gestor tentando solicitar ajuste indevidamente.',
        ]);

        $response->assertStatus(403);
    }

    // ─── Approve ────────────────────────────────

    public function test_gestor_can_approve_adjustment()
    {
        $adjustment = ClockAdjustment::create([
            'work_log_id'    => $this->workLog->id,
            'requested_by'   => $this->employeeUser->id,
            'field'          => 'clock_in',
            'requested_time' => today()->subDay()->setTime(7, 30),
            'reason'         => 'Cheguei mais cedo.',
            'status'         => AdjustmentStatus::Pending,
        ]);

        $response = $this->actingAs($this->gestor)->patch(
            route('adjustments.review', $adjustment),
            [
                'status'           => 'approved',
                'reviewer_comment' => 'Aprovado conforme solicitado.',
            ]
        );

        $response->assertRedirect(route('adjustments.index'));

        $adjustment->refresh();
        $this->assertEquals(AdjustmentStatus::Approved, $adjustment->status);
        $this->assertNotNull($adjustment->reviewed_by);

        // WorkLog should be updated
        $this->workLog->refresh();
        $this->assertEquals('07:30:00', $this->workLog->clock_in->format('H:i:s'));
    }

    public function test_gestor_can_reject_adjustment()
    {
        $adjustment = ClockAdjustment::create([
            'work_log_id'    => $this->workLog->id,
            'requested_by'   => $this->employeeUser->id,
            'field'          => 'clock_out',
            'requested_time' => today()->subDay()->setTime(18, 0),
            'reason'         => 'Saí mais tarde.',
            'status'         => AdjustmentStatus::Pending,
        ]);

        $response = $this->actingAs($this->gestor)->patch(
            route('adjustments.review', $adjustment),
            [
                'status'           => 'rejected',
                'reviewer_comment' => 'Sem evidência.',
            ]
        );

        $response->assertRedirect(route('adjustments.index'));

        $adjustment->refresh();
        $this->assertEquals(AdjustmentStatus::Rejected, $adjustment->status);

        // WorkLog should NOT be updated
        $this->workLog->refresh();
        $this->assertEquals('17:00:00', $this->workLog->clock_out->format('H:i:s'));
    }

    public function test_employee_cannot_review_adjustment()
    {
        $adjustment = ClockAdjustment::create([
            'work_log_id'    => $this->workLog->id,
            'requested_by'   => $this->employeeUser->id,
            'field'          => 'clock_in',
            'requested_time' => today()->subDay()->setTime(7, 30),
            'reason'         => 'Cheguei mais cedo.',
            'status'         => AdjustmentStatus::Pending,
        ]);

        $response = $this->actingAs($this->employeeUser)->patch(
            route('adjustments.review', $adjustment),
            ['status' => 'approved']
        );

        $response->assertStatus(403);
    }

    // ─── Index ──────────────────────────────────

    public function test_gestor_sees_all_adjustments()
    {
        $response = $this->actingAs($this->gestor)->get(route('adjustments.index'));
        $response->assertStatus(200);
    }

    public function test_employee_sees_only_own_adjustments()
    {
        $response = $this->actingAs($this->employeeUser)->get(route('adjustments.index'));
        $response->assertStatus(200);
    }

    // ─── Validation ─────────────────────────────

    public function test_adjustment_requires_minimum_reason()
    {
        $response = $this->actingAs($this->employeeUser)->post(route('adjustments.store'), [
            'work_log_id'    => $this->workLog->id,
            'field'          => 'clock_in',
            'requested_time' => today()->subDay()->setTime(7, 30)->format('Y-m-d H:i:s'),
            'reason'         => 'curto', // less than 10 chars
        ]);

        $response->assertSessionHasErrors('reason');
    }

    public function test_adjustment_validates_field()
    {
        $response = $this->actingAs($this->employeeUser)->post(route('adjustments.store'), [
            'work_log_id'    => $this->workLog->id,
            'field'          => 'invalid_field',
            'requested_time' => today()->subDay()->setTime(7, 30)->format('Y-m-d H:i:s'),
            'reason'         => 'Justificativa com mais de 10 caracteres.',
        ]);

        $response->assertSessionHasErrors('field');
    }
}
