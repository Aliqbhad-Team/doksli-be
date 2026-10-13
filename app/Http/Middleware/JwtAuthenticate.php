<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user() ?? auth()->user();

        if (! $user) {
            $header = $request->header('Authorization', '');
            // bedakan 401 vs 403 suspended: guard sudah null untuk suspended
            // cek apakah token ada tapi decode jadi suspended user
            if ($header && str_starts_with((string) $header, 'Bearer ')) {
                // coba decode untuk deteksi suspended
                try {
                    $token = trim(substr((string) $header, 7));
                    $payload = app(\App\Services\JwtService::class)->decode($token);
                    $id = $payload->sub ?? null;
                    if ($id) {
                        $u = \App\Models\User::find($id);
                        if ($u) {
                            $status = $u->getAttribute('status');
                            $val = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
                            if ($val === 'suspended') {
                                return response()->json(['message' => 'Akun ditangguhkan.'], 403);
                            }
                        }
                    }
                } catch (\Throwable) {
                    // fallthrough to 401
                }
            }

            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! auth()->user()) auth()->setUser($user);
        if (! auth('api')->user()) auth('api')->setUser($user);

        return $next($request);
    }
}
