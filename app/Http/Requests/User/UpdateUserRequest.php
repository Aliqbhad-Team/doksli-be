<?php

namespace App\Http\Requests\User;

use App\Enums\UserStatus;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('user')) ?? false;
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
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:190', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'required', 'string', Password::min(8)],
            'role_id' => ['sometimes', 'required', 'uuid', Rule::exists('roles', 'id'), function (string $attr, mixed $value, \Closure $fail) {
                $role = Role::find($value);
                if ($role && strtolower($role->name) === 'admin') {
                    $fail('Tidak boleh mengubah role menjadi admin.');
                }
            }],
            'unit_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('units', 'id')],
            'status' => ['sometimes', 'required', Rule::enum(UserStatus::class)],
        ];
    }
}
