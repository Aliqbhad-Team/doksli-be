<?php

namespace App\Http\Requests\DocumentVersion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentVersionRequest extends FormRequest
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
            'document_id' => ['required', 'uuid', Rule::exists('documents', 'id')],
            'version_number' => ['nullable', 'integer', 'min:1'],
            'file' => ['nullable', 'file'],
            'file_url' => ['required_without:file', 'nullable', 'string', 'max:500'],
            'checksum' => ['required_without:file', 'nullable', 'string', 'size:64'],
            'size_bytes' => ['required_without:file', 'nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
            'uploaded_by' => ['sometimes', 'nullable', 'uuid', Rule::exists('users', 'id')],
        ];
    }
}
