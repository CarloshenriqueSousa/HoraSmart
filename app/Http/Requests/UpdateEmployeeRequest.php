<?php

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

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $this->route('employee')->user_id],
            'cpf'      => ['required', 'string', 'size:14', 'unique:employees,cpf,' . $employeeId],
            'address'  => ['required', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:100'],
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
            'hired_at.required' => 'A data de admissão é obrigatória.',
            'hired_at.before_or_equal' => 'A data de admissão não pode ser futura.',
        ];
    }
}