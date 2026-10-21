<?php

namespace App\Http\Requests\Document;

use App\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('document')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'folder_id' => ['sometimes', 'required', 'uuid', Rule::exists('folders', 'id')],
            'category_id' => ['sometimes', 'required', 'string', Rule::exists('categories', 'id')],
            'owner_id' => ['sometimes', 'required', 'uuid', Rule::exists('users', 'id')],
            'status' => ['sometimes', 'required', Rule::enum(DocumentStatus::class)],
            'deleted_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
