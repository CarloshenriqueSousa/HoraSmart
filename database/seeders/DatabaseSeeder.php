<?php

namespace Database\Seeders;

use App\Models\Employeer;
use App\Models\User;
use App\Models\WorksLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gestor padrão
        $gestor = User::create([
            'name'     => 'Admin Smart',
            'email'    => 'gestor@smart.com',
            'password' => Hash::make('password'),
            'role'     => 'gestor',
        ]);

        // 5 funcionários com histórico de 30 dias
        $positions = [
            'Analista de TI',
            'Técnico de Redes',
            'Suporte N1',
            'Desenvolvedor',
            'Assistente Administrativo',
        ];

        $employees = [
            ['name' => 'Carlos Silva',    'email' => 'carlos@smart.com'],
            ['name' => 'Ana Souza',       'email' => 'ana@smart.com'],
            ['name' => 'Pedro Oliveira',  'email' => 'pedro@smart.com'],
            ['name' => 'Julia Santos',    'email' => 'julia@smart.com'],
            ['name' => 'Marcos Lima',     'email' => 'marcos@smart.com'],
        ];

        foreach ($employees as $index => $data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make('password'),
                'role'     => 'employee',
            ]);

            $employee = Employeer::create([
                'user_id'  => $user->id,
                'cpf'      => $this->fakeCpf(),
                'address'  => 'Rua das Flores, ' . rand(100, 999) . ', Fortaleza - CE',
                'position' => $positions[$index],
                'hired_at' => now()->subMonths(rand(3, 24)),
            ]);

            // 30 dias de histórico (dias úteis)
            $date = now()->subDays(30);
            while ($date <= now()->subDay()) {
                // Pula fins de semana
                if ($date->isWeekend()) {
                    $date->addDay();
                    continue;
                }

                $clockIn  = $date->copy()->setTime(rand(7, 9), rand(0, 59));
                $lunchOut = $clockIn->copy()->addHours(4)->addMinutes(rand(0, 30));
                $lunchIn  = $lunchOut->copy()->addMinutes(rand(45, 75));
                $clockOut = $lunchIn->copy()->addHours(4)->addMinutes(rand(0, 30));

                $worked = (
                    ($lunchOut->timestamp - $clockIn->timestamp) +
                    ($clockOut->timestamp - $lunchIn->timestamp)
                ) / 60;

                WorksLog::create([
                    'employee_id'    => $employee->id,
                    'work_date'      => $date->toDateString(),
                    'clock_in'       => $clockIn,
                    'lunch_out'      => $lunchOut,
                    'lunch_in'       => $lunchIn,
                    'clock_out'      => $clockOut,
                    'minutes_worked' => (int) $worked,
                    'status'         => 'complete',
                ]);

                $date->addDay();
            }
        }
    }

    private function fakeCpf(): string
    {
        $n  = array_map(fn() => rand(0, 9), range(1, 9));
        $d1 = array_sum(array_map(fn($i) => $n[$i] * (10 - $i), range(0, 8))) % 11;
        $d1 = $d1 < 2 ? 0 : 11 - $d1;
        $d2 = array_sum(array_map(fn($i) => $n[$i] * (11 - $i), range(0, 8))) % 11;
        $d2 = ($d2 + $d1 * 2) % 11;
        $d2 = $d2 < 2 ? 0 : 11 - $d2;
        return vsprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', [...$n, $d1, $d2]);
    }
}