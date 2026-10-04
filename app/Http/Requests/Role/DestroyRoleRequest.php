<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class DestroyRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('role')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
