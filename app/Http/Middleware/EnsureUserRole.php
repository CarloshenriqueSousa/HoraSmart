<?php

/**
 * Middleware: EnsureUserRole — Controle de acesso por perfil de usuário.
 *
 * Verifica se o usuário autenticado possui um dos roles esperados.
 * Usado nas rotas via: ->middleware('role:gestor') ou ->middleware('role:employee')
 *
 * Aceita múltiplos roles: ->middleware('role:gestor,employee')
 * Se o usuário não tiver o role correto, retorna HTTP 403.
 *
 * Registrado como alias 'role' no bootstrap/app.php.
 *
 * Tecnologias: Laravel Middleware, Variadic parameters
 *
 * @see \App\Models\User::$role
 * @see bootstrap/app.php (registro do alias)
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Acesso não autorizado.');
        }

        return $next($request);
    }
}