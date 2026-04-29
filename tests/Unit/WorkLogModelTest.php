<?php

namespace Tests\Unit;

use App\Enums\WorkLogStatus;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkLogModelTest extends TestCase
{
    use RefreshDatabase;

    private WorkLog $log;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id'            => $user->id,
            'cpf'                => '222.333.444-05',
            'position'           => 'Dev',
            'address'            => 'Test',
            'hired_at'           => now()->toDateString(),
            'daily_workload'     => 480,
            'overtime_tolerance' => 10,
        ]);

        $this->log = WorkLog::create([
            'employee_id'    => $employee->id,
            'work_date'      => '2023-01-02',
            'clock_in'       => '2023-01-02 08:00:00',
            'lunch_out'      => '2023-01-02 12:00:00',
            'lunch_in'       => '2023-01-02 13:00:00',
            'clock_out'      => '2023-01-02 17:00:00',
            'minutes_worked' => 480,
            'status'         => WorkLogStatus::Complete,
        ]);
    }

    public function test_formatted_hours_displays_correctly()
    {
        $this->assertEquals('08:00', $this->log->formatted_hours);
    }

    public function test_formatted_hours_null_displays_dashes()
    {
        $this->log->update(['minutes_worked' => null]);
        $this->assertEquals('--:--', $this->log->formatted_hours);
    }

    public function test_morning_minutes_calculated()
    {
        // 08:00 → 12:00 = 240 min
        $this->assertEquals(240, $this->log->morning_minutes);
    }

    public function test_afternoon_minutes_calculated()
    {
        // 13:00 → 17:00 = 240 min
        $this->assertEquals(240, $this->log->afternoon_minutes);
    }

    public function test_lunch_minutes_calculated()
    {
        // 12:00 → 13:00 = 60 min
        $this->assertEquals(60, $this->log->lunch_minutes);
    }

    public function test_next_action_for_new_log()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id'  => $user->id,
            'cpf'      => '555.666.777-35',
            'position' => 'Test',
            'address'  => 'Test',
            'hired_at' => now()->toDateString(),
        ]);

        $newLog = WorkLog::create([
            'employee_id' => $employee->id,
            'work_date'   => today(),
            'status'      => WorkLogStatus::InProgress,
        ]);

        $this->assertEquals('clock_in', $newLog->next_action);
    }

    public function test_next_action_after_clock_in()
    {
        $this->log->update(['status' => WorkLogStatus::InProgress, 'lunch_out' => null, 'lunch_in' => null, 'clock_out' => null]);
        $this->log->refresh();
        $this->assertEquals('lunch_out', $this->log->next_action);
    }

    public function test_next_action_returns_null_when_complete()
    {
        $this->assertNull($this->log->next_action);
    }

    public function test_is_complete_returns_true()
    {
        $this->assertTrue($this->log->isComplete());
    }

    public function test_calculate_worked_minutes_static_method()
    {
        $minutes = WorkLog::calculateWorkedMinutes($this->log);
        $this->assertEquals(480, $minutes);
    }

    public function test_recalculate_minutes_persists()
    {
        $this->log->update([
            'clock_out'      => '2023-01-02 18:00:00',
            'minutes_worked' => 0, // intentionally wrong
            'status'         => WorkLogStatus::InProgress,
        ]);
        $this->log->refresh();

        $this->log->recalculateMinutes();
        $this->log->refresh();

        $this->assertEquals(540, $this->log->minutes_worked); // 4h + 5h = 9h
        $this->assertEquals(WorkLogStatus::Complete, $this->log->status);
    }

    public function test_overtime_zero_when_no_minutes()
    {
        $this->log->update(['minutes_worked' => null]);
        $this->log->refresh();
        $this->assertEquals(0, $this->log->overtime_minutes);
    }
}
