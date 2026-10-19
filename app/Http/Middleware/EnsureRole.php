<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user() ?? auth('api')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userRole = null;
        if (method_exists($user, 'role') || isset($user->role)) {
            // eager load jika belum
            if (! $user->relationLoaded('role')) {
                $user->loadMissing('role');
            }
            $userRole = $user->role?->name ?? $user->getAttribute('role_name') ?? null;
            // jika role adalah model dengan name string, ambil string-nya
            if (is_object($userRole) && property_exists($userRole, 'name')) {
                $userRole = $userRole->name;
            }
        }

        // fallback: jika role disimpan di JWT payload
        if (! $userRole && $request->attributes->has('jwt_role')) {
            $userRole = $request->attributes->get('jwt_role');
        }

        $allowed = array_map(fn ($r) => strtolower(trim($r)), $roles);
        if ($userRole && in_array(strtolower((string) $userRole), $allowed, true)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden.'], 403);
    }
}
