<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $grade = $this->route('grade');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:grades,name,' . $grade->id . ',id,branch_id,' . ($this->input('branch_id') ?? $grade->branch_id)],
            'level' => ['required', 'integer', 'min:1', 'max:12'],
            'monthly_tuition_fee' => ['required', 'numeric', 'gte:0'],
            'education_level' => ['nullable', 'in:preschool,primary'],
            'description' => ['nullable', 'string', 'max:1000'],
            'branch_id' => ['required', 'exists:branches,id'],
        ];
    }
}
