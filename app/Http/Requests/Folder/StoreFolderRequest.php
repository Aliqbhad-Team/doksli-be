<?php

namespace App\Http\Requests\Folder;

use Illuminate\Foundation\Http\FormRequest;

class StoreFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules()
    {
        
        $user = $this->user();
        
        // Pengecekan role agar lebih aman dari error null
        $isAdmin = (isset($user->role->name) && $user->role->name === 'Admin') || $user->role === 'Admin';

        return [
            'name'             => 'required|string|max:255',
            'parent_folder_id' => 'nullable|uuid|exists:folders,id',
            // Jika Admin, unit_id wajib diisi dan harus ada di tabel units. Jika Pengelola, bersifat nullable/opsional.
            'unit_id'          => $isAdmin ? 'required|uuid|exists:units,id' : 'nullable|uuid|exists:units,id',
        ];
    }
}