<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'term_id' => ['required', 'exists:terms,id'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'attendance_date' => ['required', 'date'],
            'records' => ['required', 'array', 'min:1'],
            'records.*.student_id' => ['required', 'exists:students,id'],
            'records.*.status' => ['required', 'in:' . implode(',', Attendance::STATUSES)],
            'records.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'records.*.check_out_time' => ['nullable', 'date_format:H:i'],
            'records.*.reason' => ['nullable', 'string', 'max:100'],
            'records.*.note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('records', []) as $index => $record) {
                if (($record['status'] ?? null) === 'excused' && blank($record['reason'] ?? null)) {
                    $validator->errors()->add("records.{$index}.reason", 'សូមបញ្ចូលមូលហេតុសុំច្បាប់។');
                }
            }
        });
    }
}
