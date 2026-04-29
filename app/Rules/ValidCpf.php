<?php

/**
 * Rule: ValidCpf — Validação dos dígitos verificadores do CPF.
 *
 * Verifica se o CPF informado possui dígitos verificadores válidos
 * de acordo com o algoritmo oficial da Receita Federal.
 *
 * Uso:
 *   'cpf' => ['required', 'string', 'size:14', new ValidCpf]
 *
 * @see \App\Http\Requests\StoreEmployeeRequest
 */

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Remove formatação
        $cpf = preg_replace('/\D/', '', $value);

        // Deve ter 11 dígitos
        if (strlen($cpf) !== 11) {
            $fail('O CPF informado é inválido.');
            return;
        }

        // Rejeita sequências repetidas (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            $fail('O CPF informado é inválido.');
            return;
        }

        // Cálculo do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $d1 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        if ((int) $cpf[9] !== $d1) {
            $fail('O CPF informado é inválido.');
            return;
        }

        // Cálculo do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $d2 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        if ((int) $cpf[10] !== $d2) {
            $fail('O CPF informado é inválido.');
            return;
        }
    }
}
