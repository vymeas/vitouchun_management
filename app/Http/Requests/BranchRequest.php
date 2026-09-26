<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $branchId = $this->route('branch')?->id;

        return [
            'code'      => 'required|string|max:20|unique:branches,code,' . $branchId,
            'name'      => 'required|string|max:255',
            'name_kh'   => 'nullable|string|max:255',
            'address'   => 'nullable|string|max:1000',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'nullable|email|max:255',
            'status'    => 'required|in:active,inactive,closed',
            'opened_at' => 'nullable|date',
            'closed_at' => 'nullable|date|after_or_equal:opened_at',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'    => 'សូមបញ្ចូលលេខកូដសាខា។',
            'code.unique'      => 'លេខកូដសាខានេះមានរួចហើយ។',
            'name.required'    => 'សូមបញ្ចូលឈ្មោះសាខា។',
            'status.required'  => 'សូមជ្រើសរើសសភាព។',
        ];
    }
}
