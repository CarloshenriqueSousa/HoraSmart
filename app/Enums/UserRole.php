<?php

/**
 * Enum: UserRole — Roles de acesso do sistema.
 *
 * Define os dois níveis de acesso:
 *  - Gestor:   Acesso total (CRUD funcionários, aprovação, relatórios)
 *  - Employee: Acesso restrito (ponto, solicitações, dados próprios)
 *
 * Substitui strings mágicas ('gestor', 'employee') por um tipo seguro.
 *
 * @see \App\Models\User
 * @see \App\Http\Middleware\EnsureUserRole
 */

namespace App\Enums;

enum UserRole: string
{
    case Gestor   = 'gestor';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::Gestor   => 'Gestor de RH',
            self::Employee => 'Funcionário',
        };
    }
}
