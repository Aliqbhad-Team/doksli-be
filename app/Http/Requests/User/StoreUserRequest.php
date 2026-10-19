<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (! $u) return false;
        $u->loadMissing('role');
        $role = strtolower((string) ($u->role?->name ?? ''));
        return in_array($role, ['admin', 'pengelola'], true);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->email)) {
            $this->merge(['email' => mb_strtolower(trim($this->email))]);
        }

        // Pengelola: auto unit dari unit pengelola, abaikan input unit_id jika ada
        $actor = $this->user();
        if ($actor) {
            $actor->loadMissing('role');
            if (strtolower((string) ($actor->role?->name ?? '')) === 'pengelola') {
                // Jika pengelola tidak punya unit, biarkan validasi fail (required)
                // Jika punya unit, force unit_id = unit pengelola
                if ($actor->unit_id) {
                    $this->merge(['unit_id' => $actor->unit_id]);
                }
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $actor = $this->user();
        $actorRole = $actor ? strtolower((string) ($actor->loadMissing('role')->role?->name ?? '')) : '';

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)],
            'role_id' => ['required', 'uuid', Rule::exists('roles', 'id'), function (string $attr, mixed $value, \Closure $fail) use ($actorRole) {
                $role = Role::find($value);
                if (! $role) return;
                $target = strtolower((string) $role->name);
                if ($target === 'admin') {
                    $fail('Tidak boleh membuat user dengan role admin. Admin hanya 1 dari seeder.');
                }
                if ($actorRole === 'pengelola' && $target !== 'pengguna') {
                    $fail('Pengelola hanya boleh membuat akun dengan role Pengguna.');
                }
                if ($actorRole === 'admin' && ! in_array($target, ['pengelola', 'pengguna'], true)) {
                    $fail('Admin hanya boleh membuat akun dengan role Pengelola atau Pengguna.');
                }
            }],
            // Admin wajib pilih unit, Pengelola auto (sudah di-merge di atas)
            'unit_id' => $actorRole === 'pengelola'
                ? ['required', 'uuid', Rule::exists('units', 'id')]
                : ['required', 'uuid', Rule::exists('units', 'id')],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required' => 'Unit wajib diisi.',
        ];
    }
}
