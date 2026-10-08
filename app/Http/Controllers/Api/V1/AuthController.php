<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request, JwtService $jwt): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $status = $user->getAttribute('status');
        $val = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
        if ($val === 'suspended') {
            return response()->json(['message' => 'Akun ditangguhkan.'], 403);
        }

        $user->loadMissing('role');
        $roleName = $user->role?->name;

        $token = $jwt->issue($user->id, $roleName);
        $refresh = $jwt->issueRefresh($user->id);

        return response()->json([
            'token' => $token,
            'refresh_token' => $refresh,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
            'user' => UserResource::make($user->load(['role', 'unit'])),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user() ?? auth('api')->user();
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $user->loadMissing(['role', 'unit']);

        return response()->json(['data' => UserResource::make($user)]);
    }

    public function refresh(Request $request, JwtService $jwt): JsonResponse
    {
        $header = $request->header('Authorization', '');
        $token = null;
        if (is_string($header) && str_starts_with($header, 'Bearer ')) {
            $token = trim(substr($header, 7));
        }
        $token = $token ?: $request->input('refresh_token');

        if (! $token) {
            return response()->json(['message' => 'Refresh token tidak ada.'], 422);
        }

        try {
            $payload = $jwt->decode($token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Token tidak valid.'], 401);
        }

        if (($payload->type ?? null) !== 'refresh') {
            return response()->json(['message' => 'Token bukan refresh token.'], 422);
        }

        $user = User::find($payload->sub ?? null);
        if (! $user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        $status = $user->getAttribute('status');
        $val = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
        if ($val === 'suspended') {
            return response()->json(['message' => 'Akun ditangguhkan.'], 403);
        }

        $user->loadMissing('role');
        $newToken = $jwt->issue($user->id, $user->role?->name);

        return response()->json([
            'token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => (int) config('jwt.ttl', 60) * 60,
        ]);
    }

    public function logout(): JsonResponse
    {
        // Stateless JWT: client hapus token. Server tidak simpan blacklist di versi ini.
        return response()->json(['message' => 'Logout berhasil.']);
    }
}
