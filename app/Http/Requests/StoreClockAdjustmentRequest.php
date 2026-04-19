<?php

/**
 * Form Request: Validação para criação de solicitação de ajuste de ponto.
 *
 * Garante que:
 *  - Apenas funcionários (role=employee) podem solicitar
 *  - O work_log_id existe na tabela work_logs
 *  - O campo a corrigir é um dos 4 campos válidos
 *  - O horário correto é uma data/hora válida
 *  - A justificativa tem entre 10 e 500 caracteres
 *
 * O método prepareForValidation() converte o formato datetime-local (Y-m-d\TH:i)
 * enviado pelo HTML5 para o formato padrão do banco (Y-m-d H:i:s).
 *
 * @see \App\Http\Controllers\ClockAdjustmentController::store()
 * @see \App\Models\ClockAdjustment
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isEmployee();
    }

    protected function prepareForValidation(): void
    {
        if ($this->requested_time && str_contains($this->requested_time, 'T')) {
            $this->merge([
                'requested_time' => str_replace('T', ' ', $this->requested_time) . ':00',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'work_log_id'    => ['required', 'exists:work_logs,id'],
            'field'          => ['required', 'in:clock_in,lunch_out,lunch_in,clock_out'],
            'requested_time' => ['required', 'date'],
            'reason'         => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_log_id.required'    => 'O registro de ponto é obrigatório.',
            'work_log_id.exists'      => 'Registro de ponto não encontrado.',
            'field.required'          => 'O campo a corrigir é obrigatório.',
            'field.in'                => 'Campo inválido.',
            'requested_time.required' => 'O horário correto é obrigatório.',
            'requested_time.date'     => 'Formato de horário inválido.',
            'reason.required'         => 'A justificativa é obrigatória.',
            'reason.min'              => 'A justificativa deve ter no mínimo 10 caracteres.',
        ];
    }
}