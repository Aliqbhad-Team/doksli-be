<?php

namespace App\Http\Requests\FolderPermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'folder_id'    => ['required', 'string', 'exists:folders,id'],
            'subject_type' => ['required', 'string', Rule::in(['user', 'group'])],
            'subject_id'   => ['required', 'string'], 
            'level'        => ['required', 'string', Rule::in(['view', 'download', 'edit', 'manage'])],
        ];
    }
}