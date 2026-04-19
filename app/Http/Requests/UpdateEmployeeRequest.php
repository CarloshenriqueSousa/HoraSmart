<?php

/**
 * Form Request: Validação para atualização de funcionário (via gestor).
 *
 * Similar ao StoreEmployeeRequest, mas:
 *  - Não exige senha (na edição, senha não é alterada)
 *  - Regras de unique ignoram o registro atual (email do user, cpf do employee)
 *
 * Tecnologias: Laravel Form Request, Validation Rules
 *
 * @see \App\Http\Controllers\EmployeeController::update()
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isGestor();
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')->id;

        $maxMinutes = 480; // 8h defaults to CLT
        if ($this->input('employee_type') === 'Estagiário') {
             $maxMinutes = 240; // 4h
        } elseif ($this->input('employee_type') === 'Trainee') {
             $maxMinutes = 360; // 6h
        }

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $this->route('employee')->user_id],
            'cpf'      => ['required', 'string', 'size:14', 'unique:employees,cpf,' . $employeeId],
            'address'  => ['required', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:100'],
            'employee_type' => ['required', 'string', 'in:Estagiário,Trainee,CLT'],
            'shift'         => ['required', 'string', 'in:morning,afternoon,night'],
            'daily_workload' => ['required', 'integer', 'min:60', "max:$maxMinutes"],
            'overtime_tolerance' => ['required', 'integer', 'min:0', 'max:10'],
            'hired_at' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'O nome é obrigatório.',
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'email.unique'      => 'Este e-mail já está em uso.',
            'cpf.required'      => 'O CPF é obrigatório.',
            'cpf.size'          => 'O CPF deve ter 14 caracteres (ex: 000.000.000-00).',
            'cpf.unique'        => 'Este CPF já está cadastrado.',
            'address.required'  => 'O endereço é obrigatório.',
            'position.required' => 'O cargo é obrigatório.',
            'employee_type.in'  => 'O tipo de funcionário é inválido.',
            'shift.in'          => 'O turno é inválido.',
            'daily_workload.max'=> 'A carga horária informada excede o limite legal para esta categoria.',
            'overtime_tolerance.max' => 'A tolerância máxima permitida é de 10 minutos.',
            'hired_at.required' => 'A data de admissão é obrigatória.',
            'hired_at.before_or_equal' => 'A data de admissão não pode ser futura.',
        ];
    }
}