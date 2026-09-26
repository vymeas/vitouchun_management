<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScoresRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'scores' => ['required', 'array', 'min:1'],
            'scores.*.student_id' => ['required', 'exists:students,id'],
            'scores.*.score' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
