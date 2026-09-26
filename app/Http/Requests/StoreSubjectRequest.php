<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSubjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50'],
            'name_kh' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'level' => ['required', 'in:preschool,primary'],
            'parent_subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'credit' => ['required', 'integer', 'min:0', 'max:100'],
            'type' => ['required', 'string', 'max:50'],
            'subject_type' => ['nullable', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:2000'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'grade_ids' => ['nullable', 'array'],
            'grade_ids.*' => ['integer', 'exists:grades,id'],
        ];
    }
}
