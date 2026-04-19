<?php

/**
 * Form Request: Validação para cadastro de funcionário (via gestor).
 *
 * Garante que:
 *  - Apenas gestores podem executar (authorize)
 *  - Nome, email, CPF, endereço, cargo, data de admissão e senha são obrigatórios
 *  - Email único na tabela users, CPF único na tabela employees
 *  - CPF deve ter exatamente 14 caracteres (formato 000.000.000-00)
 *  - Data de admissão não pode ser futura
 *  - Senha confirmada (password_confirmation) com mínimo 8 caracteres
 *
 * Mensagens de erro em português (pt-BR) para UX.
 *
 * Tecnologias: Laravel Form Request, Validation Rules
 *
 * @see \App\Http\Controllers\EmployeeController::store()
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isGestor();
    }

    public function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'cpf'      => ['required', 'string', 'size:14', 'unique:employees,cpf'],
            'address'  => ['required', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:100'],
            'hired_at' => ['required', 'date', 'before_or_equal:today'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            'password.required' => 'A senha é obrigatória.',
            'password.min'      => 'A senha deve ter no mínimo 8 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ];
    }
}