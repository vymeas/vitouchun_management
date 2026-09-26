<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }
    public function rules(): array
    {
        return ['status' => ['required', 'in:' . implode(',', Attendance::STATUSES)], 'check_in_time' => ['nullable', 'date_format:H:i'], 'check_out_time' => ['nullable', 'date_format:H:i'], 'reason' => ['nullable', 'string', 'max:100'], 'note' => ['nullable', 'string', 'max:500']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'excused' && blank($this->input('reason'))) $validator->errors()->add('reason', 'សូមបញ្ចូលមូលហេតុសុំច្បាប់។');
        });
    }
}
