<?php

namespace App\Http\Requests\Group;

use App\Models\Group;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Group::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('groups', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}