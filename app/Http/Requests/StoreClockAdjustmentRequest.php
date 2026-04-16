<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isEmployee();
    }

    public function rules(): array
    {
        return [
            'work_log_id'    => ['required', 'exists:work_logs,id'],
            'field'          => ['required', 'in:clock_in,lunch_out,lunch_in,clock_out'],
            'requested_time' => ['required', 'date_format:Y-m-d H:i:s'],
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
            'requested_time.date_format' => 'Formato de horário inválido.',
            'reason.required'         => 'A justificativa é obrigatória.',
            'reason.min'              => 'A justificativa deve ter no mínimo 10 caracteres.',
        ];
    }
}