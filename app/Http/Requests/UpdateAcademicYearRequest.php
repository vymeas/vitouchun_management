<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $academicYear = $this->route('academic_year');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:academic_years,name,' . $academicYear->id . ',id,branch_id,' . ($this->input('branch_id') ?? $academicYear->branch_id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'branch_id' => ['required', 'exists:branches,id'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
