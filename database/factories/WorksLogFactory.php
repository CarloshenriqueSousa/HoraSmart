<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Employeer;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorksLogFactory extends Factory
{
    public function definition(): array
    {
        $clockIn   = $this->faker->dateTimeBetween('07:45:00', '09:00:00');
        $lunchOut  = (clone $clockIn)->modify('+4 hours')->modify('+' . rand(0, 30) . ' minutes');
        $lunchIn   = (clone $lunchOut)->modify('+' . rand(45, 75) . ' minutes');
        $clockOut  = (clone $lunchIn)->modify('+4 hours')->modify('+' . rand(0, 30) . ' minutes');

        $worked = (
            ($lunchOut->getTimestamp() - $clockIn->getTimestamp()) +
            ($clockOut->getTimestamp() - $lunchIn->getTimestamp())
        ) / 60;

        return [
            'employee_id'    => Employeer::factory(),
            'work_date'      => $this->faker->dateTimeBetween('-30 days', 'now'),
            'clock_in'       => $clockIn,
            'lunch_out'      => $lunchOut,
            'lunch_in'       => $lunchIn,
            'clock_out'      => $clockOut,
            'minutes_worked' => (int) $worked,
            'status'         => 'complete',
        ];
    }
}