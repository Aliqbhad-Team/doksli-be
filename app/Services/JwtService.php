<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

class JwtService
{
    public function issue(string $userId, ?string $roleName = null): string
    {
        $now = time();
        $ttl = (int) config('jwt.ttl', 60);
        $payload = [
            'iss' => config('app.url'),
            'sub' => $userId,
            'role' => $roleName,
            'iat' => $now,
            'exp' => $now + ($ttl * 60),
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algo', 'HS256'));
    }

    public function issueRefresh(string $userId): string
    {
        $now = time();
        $ttl = (int) config('jwt.refresh_ttl', 20160);
        $payload = [
            'iss' => config('app.url'),
            'sub' => $userId,
            'type' => 'refresh',
            'iat' => $now,
            'exp' => $now + ($ttl * 60),
            'jti' => (string) Str::uuid(),
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algo', 'HS256'));
    }

    /** @return object */
    public function decode(string $token): object
    {
        if (config('jwt.leeway', 0) > 0) {
            JWT::$leeway = (int) config('jwt.leeway');
        }

        return JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo', 'HS256')));
    }
}
