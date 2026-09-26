<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $student = $this->route('student');

        return [
            'khmer_name' => ['required', 'string', 'max:255'],
            'english_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:M,F'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'current_address' => ['nullable', 'string', 'max:1000'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_occupation' => ['nullable', 'string', 'max:255'],
            'father_phone' => ['nullable', 'string', 'max:30'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_occupation' => ['nullable', 'string', 'max:255'],
            'mother_phone' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'student_type' => ['required', 'string', 'max:255'],
            'health_condition' => ['nullable', 'string', 'max:1000'],
            'characteristics' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:active,inactive,graduated,transferred'],
            'branch_id' => ['required', 'exists:branches,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
