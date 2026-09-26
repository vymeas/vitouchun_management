<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'username'     => 'required|string|max:50|unique:users,username,' . $userId,
            'full_name'    => 'required|string|max:255',
            'full_name_kh' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255|unique:users,email,' . $userId,
            'phone'        => 'nullable|string|max:20',
            'branch_id'    => 'nullable|exists:branches,id',
            'role'         => 'required|in:super_admin,admin,accountant,registrar,teacher,staff',
            'status'       => 'required|in:active,inactive,suspended',
            'password'     => $isUpdate
                ? 'nullable|confirmed|min:8'
                : ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required'    => 'សូមបញ្ចូលឈ្មោះអ្នកប្រើ។',
            'username.unique'      => 'ឈ្មោះអ្នកប្រើនេះមានរួចហើយ។',
            'full_name.required'   => 'សូមបញ្ចូលឈ្មោះពេញ។',
            'email.unique'         => 'អ៊ីម៉ែលនេះមានរួចហើយ។',
            'role.required'        => 'សូមជ្រើសរើសតួនាទី។',
            'status.required'      => 'សូមជ្រើសរើសសភាព។',
            'password.required'    => 'សូមបញ្ចូលពាក្យសម្ងាត់។',
            'password.confirmed'   => 'ពាក្យសម្ងាត់មិនត្រូវគ្នា។',
            'password.min'         => 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងហោចណាស់ 8 តួ។',
        ];
    }
}
