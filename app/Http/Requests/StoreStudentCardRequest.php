<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentCardRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return ['student_id' => ['required', 'exists:students,id'], 'enrollment_id' => ['required', 'exists:enrollments,id'], 'academic_year_id' => ['required', 'exists:academic_years,id'], 'issue_date' => ['required', 'date'], 'expiry_date' => ['required', 'date', 'after_or_equal:issue_date'], 'card_type' => ['required', 'string', 'max:30']];
    }
}
