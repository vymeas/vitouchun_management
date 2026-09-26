<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $schoolClass = $this->route('class');

        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'grade_id' => ['required', 'exists:grades,id'],
            'name' => ['required', 'string', 'max:255'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'branch_id' => ['required', 'exists:branches,id'],
            'shift_id' => ['nullable', 'exists:study_shifts,id'],
        ];
    }
}
