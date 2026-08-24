<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Restrict a route to one or more roles, e.g. middleware('role:company_admin,company_manager').
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 401);

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        // Tenant isolation guard: every non-super-admin must belong to an active company.
        if ($user->role !== \App\Models\User::ROLE_SAAS_ADMIN) {
            if (! $user->company_id) {
                abort(403, 'Your account is not attached to any company.');
            }

            if ($user->company && ! $user->company->isActive() && ! $user->isClient()) {
                abort(403, 'Your company account is currently inactive.');
            }
        }

        return $next($request);
    }
}
