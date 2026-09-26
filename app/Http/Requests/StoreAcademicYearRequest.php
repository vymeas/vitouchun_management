<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:academic_years,name,NULL,id,branch_id,' . ($this->input('branch_id') ?? auth()->user()->branch_id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'branch_id' => ['required', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'សូមបញ្ចូលឈ្មោះឆ្នាំសិក្សា។',
            'branch_id.required' => 'សូមជ្រើសរើសសាខា។',
            'start_date.required' => 'សូមបញ្ចូលថ្ងៃចាប់ផ្តើម។',
            'end_date.required' => 'សូមបញ្ចូលថ្ងៃបញ្ចប់។',
            'name.unique' => 'ឆ្នាំសិក្សានេះមានរួចហើយក្នុងសាខានេះ។',
        ];
    }
}
