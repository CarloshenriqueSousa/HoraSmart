<?php

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