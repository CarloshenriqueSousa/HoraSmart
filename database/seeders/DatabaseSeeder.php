<?php

/**
 * Seeder: DatabaseSeeder — Dados de demonstração do HoraSmart.
 *
 * Cria um ambiente completo para avaliação:
 *  - 1 usuário gestor (gestor@smart.com / password)
 *  - 5 funcionários com dados realistas (nome, CPF válido, cargo, endereço em Fortaleza)
 *  - 30 dias de histórico de ponto para cada funcionário (apenas dias úteis)
 *
 * Cada dia útil recebe 4 batidas com horários randomizados:
 *  - Entrada: entre 07:00 e 09:59
 *  - Almoço: ~4h depois da entrada, duração de 45-75 min
 *  - Saída: ~4h depois do retorno do almoço
 *
 * O CPF é gerado com dígitos verificadores válidos (algoritmo real do CPF).
 * Usa firstOrCreate para idempotência — pode rodar múltiplas vezes sem erro.
 *
 * Tecnologias: Laravel Seeder, Eloquent, Carbon, firstOrCreate
 *
 * @see \App\Models\User
 * @see \App\Models\Employee
 * @see \App\Models\WorkLog
 */

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gestor padrão
        $gestor = User::firstOrCreate(
            ['email' => 'gestor@smart.com'],
            [
                'name'     => 'Admin Smart',
                'password' => Hash::make('password'),
                'role'     => 'gestor',
            ]
        );

        $employees = [
            ['name' => 'Carlos Silva',   'email' => 'carlos@smart.com'],
            ['name' => 'Ana Souza',      'email' => 'ana@smart.com'],
            ['name' => 'Pedro Oliveira', 'email' => 'pedro@smart.com'],
            ['name' => 'Julia Santos',   'email' => 'julia@smart.com'],
            ['name' => 'Marcos Lima',    'email' => 'marcos@smart.com'],
        ];

        $positions = [
            'Analista de TI',
            'Técnico de Redes',
            'Suporte N1',
            'Desenvolvedor',
            'Assistente Administrativo',
        ];

        foreach ($employees as $index => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'employee',
                ]
            );

            $employee = Employee::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'cpf'      => $this->fakeCpf(),
                    'address'  => 'Rua das Flores, ' . rand(100, 999) . ', Fortaleza - CE',
                    'position' => $positions[$index],
                    'hired_at' => now()->subMonths(rand(3, 24)),
                ]
            );

            // 30 dias de histórico (apenas dias úteis)
            $date = now()->subDays(30);
            while ($date <= now()->subDay()) {
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

                WorkLog::firstOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'work_date'   => $date->toDateString(),
                    ],
                    [
                        'clock_in'       => $clockIn,
                        'lunch_out'      => $lunchOut,
                        'lunch_in'       => $lunchIn,
                        'clock_out'      => $clockOut,
                        'minutes_worked' => (int) $worked,
                        'status'         => 'complete',
                    ]
                );

                $date->addDay();
            }
        }
    }

    /**
     * Gera um CPF válido com dígitos verificadores corretos.
     */
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