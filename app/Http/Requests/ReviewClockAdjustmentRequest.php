<?php

/**
 * Form Request: Validação para revisão de solicitação de ajuste (via gestor).
 *
 * O gestor deve informar:
 *  - status: 'approved' ou 'rejected'
 *  - reviewer_comment: opcional, até 500 caracteres
 *
 * Se aprovado, o ClockAdjustmentController atualiza o WorkLog automaticamente.
 *
 * Tecnologias: Laravel Form Request
 *
 * @see \App\Http\Controllers\ClockAdjustmentController::review()
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewClockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isGestor();
    }

    public function rules(): array
    {
        return [
            'status'           => ['required', 'in:approved,rejected'],
            'reviewer_comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'A decisão é obrigatória.',
            'status.in'       => 'Decisão inválida.',
        ];
    }
}