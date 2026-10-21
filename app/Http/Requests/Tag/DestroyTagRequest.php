<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('tag')) ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
