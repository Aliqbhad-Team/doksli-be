<?php

namespace App\Http\Requests\Folder;

use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:150'],
            'parent_folder_id' => ['nullable', 'string', 'exists:folders,id'],
            'unit_id'          => ['nullable', 'string', 'exists:units,id'],
        ];
    }
}