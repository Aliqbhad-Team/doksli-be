<?php

namespace App\Http\Requests\Group;

use Illuminate\Foundation\Http\FormRequest;

class DestroyGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('group')) ?? false;
    }

    public function rules(): array { return []; }
}
