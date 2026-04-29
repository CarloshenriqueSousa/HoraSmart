<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = \App\Models\Employee::class;

    public function definition(): array
    {
        return [
            'user_id'  => User::factory(),
            'cpf'      => $this->fakeCpf(),
            'address'  => $this->faker->streetAddress() . ', ' . $this->faker->city() . ' - ' . $this->faker->stateAbbr(),
            'position' => $this->faker->randomElement([
                'Analista de TI',
                'Técnico de Redes',
                'Suporte N1',
                'Suporte N2',
                'Desenvolvedor',
                'Coordenador de TI',
                'Assistente Administrativo',
                'Analista Financeiro',
            ]),
            'hired_at' => $this->faker->dateTimeBetween('-3 years', '-1 month'),
        ];
    }

    private function fakeCpf(): string
    {
        $n = array_map(fn() => rand(0, 9), range(1, 9));
        $d1 = array_sum(array_map(fn($i) => $n[$i] * (10 - $i), range(0, 8))) % 11;
        $d1 = $d1 < 2 ? 0 : 11 - $d1;
        $d2 = array_sum(array_map(fn($i) => $n[$i] * (11 - $i), range(0, 8))) % 11;
        $d2 = ($d2 + $d1 * 2) % 11;
        $d2 = $d2 < 2 ? 0 : 11 - $d2;
        return vsprintf('%d%d%d.%d%d%d.%d%d%d-%d%d', [...$n, $d1, $d2]);
    }
}