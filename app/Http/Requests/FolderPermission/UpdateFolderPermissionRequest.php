<?php

namespace App\Http\Requests\FolderPermission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFolderPermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            // Hanya izinkan update pada kolom level dengan pilihan yang valid
            'level' => ['required', 'string', Rule::in(['view', 'download', 'edit', 'manage'])],
        ];
    }
}