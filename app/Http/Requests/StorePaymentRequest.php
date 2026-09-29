<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'enrollment_id' => ['required', 'exists:enrollments,id'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'payment_date' => ['required', 'date'],
            'paid_until' => ['nullable', 'date', 'after_or_equal:payment_date'],
            'next_payment_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'in:USD,KHR'],
            'exchange_rate' => ['nullable', 'numeric', 'gt:0'],
            'tuition_amount' => ['nullable', 'numeric', 'gte:0'],
            'received_amount' => ['nullable', 'numeric', 'gte:0'],
            'monthly_fee' => ['nullable', 'numeric', 'gte:0'],
            'selected_months' => ['nullable', 'array'],
            'selected_months.*' => ['date_format:Y-m'],
            'administrative_months' => ['nullable', 'array'],
            'administrative_months.*' => ['date_format:Y-m'],
            'administrative_fee' => ['required', 'numeric', 'gte:0'],
            'discount_type' => ['nullable', 'in:percent,fixed'],
            'discount_amount' => ['nullable', 'numeric', 'gte:0'],
            'line_items' => ['nullable', 'array'],
            'line_items.*.description' => ['required_with:line_items.*.amount', 'nullable', 'string', 'max:255'],
            'line_items.*.amount' => ['required_with:line_items.*.description', 'nullable', 'numeric', 'gte:0'],
            'service_id' => [
                'nullable',
                'exists:services,id',
            ],

            'service_duration_months' => [
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],

            'service_unit_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'service_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'service_amount' => ['nullable', 'numeric', 'gte:0'],
            'payment_method' => ['required', 'in:cash,bank,qr,other'],
            'other_bank_name' => ['nullable', 'string', 'max:100', 'required_if:payment_method,other'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'save_and_print' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => 'សូមជ្រើសរើសសិស្ស។',
            'enrollment_id.required' => 'សូមជ្រើសរើសការចុះឈ្មោះ។',
            'selected_months.*.date_format' => 'ខែដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',
            'payment_method.required' => 'សូមជ្រើសរើសវិធីបង់ប្រាក់។',
            'exchange_rate.gt' => 'អត្រាប្តូរប្រាក់មិនត្រឹមត្រូវ។',
            'discount_amount.gte' => 'ចំនួនបញ្ចុះតម្លៃមិនត្រឹមត្រូវ។',
            'other_bank_name.required_if' => 'សូមបញ្ចូលឈ្មោះធនាគារ ឬអ្នកផ្តល់សេវាបង់ប្រាក់។',
        ];
    }
}
