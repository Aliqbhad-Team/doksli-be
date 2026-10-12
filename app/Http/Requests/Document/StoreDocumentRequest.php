<?php

namespace App\Http\Requests\Document;

use App\Enums\DocumentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'folder_id' => ['required', 'uuid', Rule::exists('folders', 'id')],
            'category_id' => ['required', 'string', Rule::exists('categories', 'id')],
            'owner_id' => ['required', 'uuid', Rule::exists('users', 'id')],
            'status' => ['sometimes', Rule::enum(DocumentStatus::class)],
            'deleted_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
