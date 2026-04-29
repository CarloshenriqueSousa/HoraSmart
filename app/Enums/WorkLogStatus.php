<?php

/**
 * Enum: WorkLogStatus — Máquina de estados da jornada de trabalho.
 *
 * Fluxo obrigatório:
 *   in_progress → on_lunch → back_from_lunch → complete
 *
 * Substitui strings mágicas por um tipo seguro com labels localizados.
 *
 * @see \App\Models\WorkLog
 * @see \App\Services\WorkLogService
 */

namespace App\Enums;

enum WorkLogStatus: string
{
    case InProgress    = 'in_progress';
    case OnLunch       = 'on_lunch';
    case BackFromLunch = 'back_from_lunch';
    case Complete      = 'complete';

    public function label(): string
    {
        return match ($this) {
            self::InProgress    => 'Trabalhando',
            self::OnLunch       => 'No almoço',
            self::BackFromLunch => 'Retornou do almoço',
            self::Complete      => 'Jornada completa',
        };
    }

    public function buttonLabel(): string
    {
        return match ($this) {
            self::InProgress    => 'Registrar Saída para Almoço',
            self::OnLunch       => 'Registrar Retorno do Almoço',
            self::BackFromLunch => 'Registrar Saída Final',
            self::Complete      => 'Jornada Finalizada',
        };
    }

    /**
     * Indica se a jornada ainda aceita batidas.
     */
    public function isActive(): bool
    {
        return $this !== self::Complete;
    }
}
