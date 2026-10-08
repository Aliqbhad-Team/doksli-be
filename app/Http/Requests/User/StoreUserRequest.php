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
        return strtolower((string) ($u->role?->name ?? '')) === 'admin';
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->email)) {
            $this->merge(['email' => mb_strtolower(trim($this->email))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'string', 'email', 'max:190', Rule::unique('users', 'email')],
            'password' => ['required', 'string', Password::min(8)],
            'role_id' => ['required', 'uuid', Rule::exists('roles', 'id'), function (string $attr, mixed $value, \Closure $fail) {
                $role = Role::find($value);
                if ($role && strtolower($role->name) === 'admin') {
                    $fail('Tidak boleh membuat user dengan role admin. Admin hanya 1 dari seeder.');
                }
            }],
            'unit_id' => ['nullable', 'uuid', Rule::exists('units', 'id')],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
        ];
    }
}
