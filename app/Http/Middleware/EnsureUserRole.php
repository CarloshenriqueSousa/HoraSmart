<?php

/**
 * Middleware: EnsureUserRole — Controle de acesso por perfil de usuário.
 *
 * Aceita tanto os valores string ('gestor', 'employee') quanto os Enums,
 * pois o middleware recebe os parâmetros como string da rota.
 *
 * Uso: ->middleware('role:gestor') ou ->middleware('role:gestor,employee')
 *
 * @see \App\Enums\UserRole
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role->value, $roles)) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}