<?php

/**
 * Enum: AdjustmentStatus — Status do fluxo de ajuste de ponto.
 *
 * Fluxo: pending → approved | rejected
 *
 * @see \App\Models\ClockAdjustment
 */

namespace App\Enums;

enum AdjustmentStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'Pendente',
            self::Approved => 'Aprovado',
            self::Rejected => 'Rejeitado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending  => 'amber',
            self::Approved => 'emerald',
            self::Rejected => 'rose',
        };
    }
}
