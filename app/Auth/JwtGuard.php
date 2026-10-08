<?php

namespace App\Auth;

use App\Services\JwtService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;

class JwtGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(
        protected UserProvider $provider,
        protected Request $request,
        protected JwtService $jwt,
    ) {}

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->user !== null) {
            return $this->user;
        }

        $token = $this->getToken();
        if (! $token) {
            return null;
        }

        try {
            $payload = $this->jwt->decode($token);
        } catch (\Throwable) {
            return null;
        }

        if (($payload->type ?? null) === 'refresh') {
            return null;
        }

        $id = $payload->sub ?? null;
        if (! $id) {
            return null;
        }

        $user = $this->provider->retrieveById($id);
        if (! $user) {
            return null;
        }

        if (method_exists($user, 'getAttribute') && $user->getAttribute('status')) {
            $status = $user->getAttribute('status');
            $val = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
            if ($val === 'suspended') {
                return null;
            }
        }

        return $this->user = $user;
    }

    public function id(): mixed
    {
        $user = $this->user();
        return $user?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        return $this;
    }

    protected function getToken(): ?string
    {
        $header = $this->request->header('Authorization', '');
        if (is_string($header) && str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return null;
    }

    public function getPayload(): ?object
    {
        $token = $this->getToken();
        if (! $token) {
            return null;
        }
        try {
            return $this->jwt->decode($token);
        } catch (\Throwable) {
            return null;
        }
    }
}
