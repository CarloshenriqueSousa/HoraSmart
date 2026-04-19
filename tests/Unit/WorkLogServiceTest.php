<?php

namespace Tests\Unit;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkLogService();
    }

    public function test_can_punch_clock_in_sequence_optimally()
    {
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::create([
            'user_id' => $user->id,
            'cpf' => '11122233344',
            'position' => 'Dev',
            'address' => 'Test Address',
            'hired_at' => now()->toDateString()
        ]);

        Carbon::setTestNow(Carbon::parse('08:00:00'));

        // Action 1: Clock In
        $result1 = $this->service->punch($employee);
        
        $this->assertTrue($result1['success']);
        $log = WorkLog::first();
        $this->assertEquals('in_progress', $log->status);
        $this->assertNotNull($log->clock_in);
        $this->assertEquals('08:00:00', $log->clock_in->format('H:i:s'));

        // Jump to Lunch Out
        Carbon::setTestNow(Carbon::parse('12:00:00'));
        $result2 = $this->service->punch($employee);
        $log->refresh();
        $this->assertTrue($result2['success']);
        $this->assertEquals('on_lunch', $log->status);
        $this->assertEquals('12:00:00', $log->lunch_out->format('H:i:s'));

        // Jump to Lunch In
        Carbon::setTestNow(Carbon::parse('13:00:00'));
        $result3 = $this->service->punch($employee);
        $log->refresh();
        $this->assertTrue($result3['success']);
        $this->assertEquals('back_from_lunch', $log->status);
        $this->assertEquals('13:00:00', $log->lunch_in->format('H:i:s'));

        // Jump to Clock Out
        Carbon::setTestNow(Carbon::parse('17:00:00'));
        $result4 = $this->service->punch($employee);
        $log->refresh();
        $this->assertTrue($result4['success']);
        $this->assertEquals('complete', $log->status);
        $this->assertEquals('17:00:00', $log->clock_out->format('H:i:s'));

        // Total hours worked should be 8h (480 minutes)
        // 08 to 12 (4 hours) + 13 to 17 (4 hours) = 8 hours
        $this->assertEquals(480, $log->minutes_worked);
    }
}
