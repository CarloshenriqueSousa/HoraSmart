<?php

namespace Tests\Unit;

use App\Enums\WorkLogStatus;
use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use App\Services\WorkLogService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkLogServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkLogService $service;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkLogService();

        $user = User::factory()->create(['role' => 'employee']);
        $this->employee = Employee::create([
            'user_id'  => $user->id,
            'cpf'      => '111.222.333-96',
            'position' => 'Dev',
            'address'  => 'Test Address',
            'hired_at' => now()->toDateString(),
        ]);
    }

    public function test_can_punch_clock_in_full_sequence()
    {
        $date = '2023-01-02'; // Monday

        Carbon::setTestNow(Carbon::parse("$date 08:00:00"));

        // Action 1: Clock In
        $result1 = $this->service->punch($this->employee);

        $this->assertTrue($result1['success']);
        $log = WorkLog::first();
        $this->assertEquals(WorkLogStatus::InProgress, $log->status);
        $this->assertNotNull($log->clock_in);
        $this->assertEquals('08:00:00', $log->clock_in->format('H:i:s'));

        // Action 2: Lunch Out
        Carbon::setTestNow(Carbon::parse("$date 12:00:00"));
        $result2 = $this->service->punch($this->employee);
        $log->refresh();
        $this->assertTrue($result2['success']);
        $this->assertEquals(WorkLogStatus::OnLunch, $log->status);
        $this->assertEquals('12:00:00', $log->lunch_out->format('H:i:s'));

        // Action 3: Lunch In
        Carbon::setTestNow(Carbon::parse("$date 13:00:00"));
        $result3 = $this->service->punch($this->employee);
        $log->refresh();
        $this->assertTrue($result3['success']);
        $this->assertEquals(WorkLogStatus::BackFromLunch, $log->status);

        // Action 4: Clock Out
        Carbon::setTestNow(Carbon::parse("$date 17:00:00"));
        $result4 = $this->service->punch($this->employee);
        $log->refresh();
        $this->assertTrue($result4['success']);
        $this->assertEquals(WorkLogStatus::Complete, $log->status);

        // Total: 8h (480 min)
        $this->assertEquals(480, $log->minutes_worked);
    }

    public function test_punch_rejects_when_journey_complete()
    {
        $date = '2023-01-02';
        Carbon::setTestNow(Carbon::parse("$date 08:00:00"));

        // Complete full journey
        $this->service->punch($this->employee); // clock_in
        Carbon::setTestNow(Carbon::parse("$date 12:00:00"));
        $this->service->punch($this->employee); // lunch_out
        Carbon::setTestNow(Carbon::parse("$date 13:00:00"));
        $this->service->punch($this->employee); // lunch_in
        Carbon::setTestNow(Carbon::parse("$date 17:00:00"));
        $this->service->punch($this->employee); // clock_out

        // 5th punch should fail
        Carbon::setTestNow(Carbon::parse("$date 18:00:00"));
        $result = $this->service->punch($this->employee);

        $this->assertFalse($result['success']);
        $this->assertEquals('Jornada do dia já finalizada.', $result['message']);
    }

    public function test_overtime_calculated_correctly()
    {
        $date = '2023-01-02';
        Carbon::setTestNow(Carbon::parse("$date 08:00:00"));

        $this->service->punch($this->employee); // clock_in 08:00
        Carbon::setTestNow(Carbon::parse("$date 12:00:00"));
        $this->service->punch($this->employee); // lunch_out 12:00
        Carbon::setTestNow(Carbon::parse("$date 13:00:00"));
        $this->service->punch($this->employee); // lunch_in 13:00
        Carbon::setTestNow(Carbon::parse("$date 19:00:00")); // 2h overtime
        $this->service->punch($this->employee); // clock_out 19:00

        $log = WorkLog::first();
        // Total: 4h + 6h = 10h (600 min). Overtime: 600 - 480 = 120 min = 2h
        $this->assertEquals(600, $log->minutes_worked);
        $this->assertEquals(120, $log->overtime_minutes);
        $this->assertEquals('+02:00', $log->formatted_overtime);
    }

    public function test_overtime_respects_tolerance()
    {
        // Set employee with 10 min tolerance (default)
        $this->employee->update([
            'daily_workload'     => 480,
            'overtime_tolerance' => 10,
        ]);

        $date = '2023-01-02';
        Carbon::setTestNow(Carbon::parse("$date 08:00:00"));

        $this->service->punch($this->employee);
        Carbon::setTestNow(Carbon::parse("$date 12:00:00"));
        $this->service->punch($this->employee);
        Carbon::setTestNow(Carbon::parse("$date 13:00:00"));
        $this->service->punch($this->employee);
        Carbon::setTestNow(Carbon::parse("$date 17:05:00")); // 5 min over, within tolerance
        $this->service->punch($this->employee);

        $log = WorkLog::first();
        // 485 min total. 485 - 480 = 5 min, within 10 min tolerance
        $this->assertEquals(0, $log->overtime_minutes);
        $this->assertEquals('—', $log->formatted_overtime);
    }

    public function test_one_log_per_employee_per_day()
    {
        $date = '2023-01-02';
        Carbon::setTestNow(Carbon::parse("$date 08:00:00"));

        $this->service->punch($this->employee);
        $this->service->punch($this->employee);

        $this->assertEquals(1, WorkLog::count());
    }
}
