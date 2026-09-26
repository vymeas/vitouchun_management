@extends('layouts.app')

@section('title', 'ទទួលប្រាក់ថ្មី')
@section('page-title', 'ទទួលប្រាក់ថ្មី')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-cash-register text-primary me-2"></i>ទទួលប្រាក់ថ្មី</h2>
        <p class="page-header-subtitle">ការទូទាត់ជាដុល្លារអាមេរិក</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">ព័ត៌មានសិស្ស</h5></div>
            <div class="card-body">
                <p class="text-muted small">ស្វែងរក និងជ្រើសរើសសិស្សដែលត្រូវបង់ប្រាក់</p>
                <form method="GET" action="{{ route('payments.create') }}" class="input-group mb-3">
                    <input type="text" name="search" class="form-control" placeholder="ស្វែងរកសិស្ស..." value="{{ request('search') }}">
                    <button class="btn btn-outline-primary"><i class="fas fa-search"></i></button>
                </form>

                <div class="vstack gap-2">
                    @forelse($students as $student)
                        @php($studentEnrollment = $student->enrollments->first())
                        <div class="border rounded p-2 d-flex align-items-center gap-2 {{ $selectedStudentId == $student->id ? 'border-primary bg-light' : '' }}">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" class="rounded-circle" style="width:42px;height:42px;object-fit:cover" alt="{{ $student->name_kh }}">
                            @else
                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width:42px;height:42px"><i class="fas fa-user text-muted"></i></div>
                            @endif
                            <div class="flex-grow-1">
                                <strong>{{ $student->student_code ?? $student->code }}</strong>
                                <div>{{ $student->khmer_name ?? $student->name_kh }}</div>
                                <small class="text-muted">{{ $studentEnrollment?->schoolClass?->name ?? '—' }} · {{ $studentEnrollment?->academicYear?->name ?? '—' }}</small>
                            </div>
                            <a href="{{ route('payments.create', ['student_id' => $student->id]) }}" class="btn btn-sm {{ $selectedStudentId == $student->id ? 'btn-primary' : 'btn-outline-primary' }}">ជ្រើសរើស</a>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <p>មិនមានសិស្សដែលមានការចុះឈ្មោះសកម្មសម្រាប់បង់ប្រាក់ទេ។</p>
                            <a href="{{ route('enrollments.create') }}" class="btn btn-outline-primary btn-sm">ទៅការចុះឈ្មោះ</a>
                        </div>
                    @endforelse
                </div>

                @if($students->hasPages())
                    <div class="mt-3">{{ $students->links() }}</div>
                @endif

                @if($selectedEnrollment)
                    <div class="border border-success rounded p-3 mt-3 bg-success-subtle">
                        <div class="d-flex justify-content-between"><strong>បានជ្រើសរើស</strong><a href="{{ route('payments.create') }}" class="small">ផ្លាស់ប្តូរ</a></div>
                        <div class="mt-2">{{ $selectedEnrollment->student?->name_kh }}</div>
                        <small>{{ $selectedEnrollment->schoolClass?->name }} · គ្រូ {{ $selectedEnrollment->schoolClass?->teacher?->display_name ?? '—' }}</small>
                        <div class="small mt-1">ឆ្នាំសិក្សា: {{ $selectedAcademicYear?->name ?? '—' }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">ព័ត៌មានការបង់ប្រាក់</h5></div>
            <div class="card-body">
                <form method="POST" action="{{ route('payments.store') }}" id="payment-form">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $selectedStudentId ?: old('student_id') }}">
                    <input type="hidden" name="invoice_id" value="{{ $selectedInvoiceId ?: old('invoice_id') }}">
                    <input type="hidden" name="enrollment_id" value="{{ $selectedEnrollmentId ?: old('enrollment_id') }}">
                    <input type="hidden" name="academic_year_id" value="{{ $selectedAcademicYear?->id ?: old('academic_year_id') }}">
                    <input type="hidden" name="currency" id="currency" value="USD">
                    <input type="hidden" name="monthly_fee" id="monthly-fee" value="{{ $monthlyTuitionFee }}">
                    <input type="hidden" name="administrative_fee" id="administrative-fee" value="0">

                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">លេខយោង</label><input class="form-control" value="TF បង្កើតស្វ័យប្រវត្តិពេលរក្សាទុក" readonly></div>
                        <div class="col-md-6"><label class="form-label">ឆ្នាំសិក្សា</label><input class="form-control" value="{{ $selectedAcademicYear?->name ?? '—' }}" readonly></div>
                        <div class="col-md-6"><label class="form-label" for="payment-date">កាលបរិច្ឆេទបង់ប្រាក់ <span class="text-danger">*</span></label><input type="date" name="payment_date" id="payment-date" class="form-control" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required></div>
                        <div class="col-12"><label class="form-label">បរិយាយ</label><input name="notes" class="form-control" value="{{ old('notes') }}" placeholder="ឧ. ថ្លៃសិក្សា ខែ កញ្ញា ឆ្នាំ ២០២៦"></div>
                    </div>
                    <div class="alert alert-info border mt-3 mb-0" id="tuition-preview" data-registration-date="{{ $selectedEnrollment?->enrollment_date?->format('Y-m-d') }}" data-original-start-date="{{ $originalStartDate?->format('Y-m-d') }}" data-last-paid-until="{{ $lastPayment?->paid_until?->format('Y-m-d') }}">
                        <strong>មើលជាមុនការគណនា</strong>
                        <div class="row g-2 small mt-1">
                            <div class="col-md-4">ថ្ងៃចុះឈ្មោះ: <span id="preview-registration-date">—</span></div>
                            <div class="col-md-4">ថ្ងៃចាប់ផ្តើមដើម: <span id="preview-original-start-date">—</span></div>
                            <div class="col-md-4">ថ្ងៃបង់ប្រាក់: <span id="preview-payment-date">—</span></div>
                            <div class="col-md-4">ខែដែលបានជ្រើស: <span id="preview-selected-months">0</span></div>
                            <div class="col-md-4">ចំនួនថ្ងៃ prorate: <span id="preview-prorated-days">0</span></div>
                            <div class="col-md-4">តម្លៃប្រចាំថ្ងៃ: <span id="preview-daily-rate">—</span></div>
                            <div class="col-md-4">ត្លៃសិក្សា: <span id="preview-tuition">$0.00</span></div>
                            <div class="col-md-6">បានបង់រហូតដល់: <span id="preview-paid-until">—</span></div>
                            <div class="col-md-6">ថ្ងៃបង់បន្ទាប់: <span id="preview-next-payment">—</span></div>
                        </div>
                    </div>

                    <hr>
                    <style>
                        .payment-month-picker {
                            border: 1px solid #dee2e6;
                            border-radius: 10px;
                            padding: 1rem;
                            background: #fff;
                        }
                        .payment-month-grid {
                            display: grid;
                            grid-template-columns: repeat(3, minmax(0, 1fr));
                            gap: .75rem 1.25rem;
                            padding: .25rem .5rem;
                        }
                        .administrative-month-grid {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                        }
                        .payment-month-option {
                            display: flex;
                            align-items: center;
                            gap: .5rem;
                            min-width: 0;
                            margin: 0;
                            padding: .5rem .75rem;
                            border-radius: .375rem;
                            padding-left: .75rem;
                        }
                        .payment-month-option .form-check-input {
                            float: none;
                            flex: 0 0 auto;
                            margin: 0;
                        }
                        .payment-month-option .form-check-label {
                            min-width: 0;
                            color: #212529;
                            overflow-wrap: anywhere;
                        }
                        .payment-month-option.is-disabled .form-check-label {
                            color: #6c757d;
                        }
                        .payment-month-option input:disabled {
                            cursor: not-allowed;
                        }
                        .administrative-check-all {
                            display: inline-flex;
                            align-items: center;
                            gap: .5rem;
                            padding-left: 0;
                        }
                        .administrative-check-all .form-check-input {
                            float: none;
                            margin: 0;
                        }
                        @media (max-width: 991.98px) {
                            .payment-month-grid,
                            .administrative-month-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                        }
                        @media (max-width: 575.98px) {
                            .payment-month-grid,
                            .administrative-month-grid { grid-template-columns: 1fr; }
                        }
                    </style>
                    <div class="payment-month-picker">
                        <label class="form-label fw-semibold mb-3">ជ្រើសរើសខែ <span class="text-danger">*</span></label>
                        <div class="payment-month-grid">
                        @forelse($monthlyPeriods as $period)
                            @php($monthKey = $period->format('Y-m'))
                            @php($isPaidMonth = in_array($monthKey, $paidMonthKeys, true))
                            @php($isLockedMonth = in_array($monthKey, $lockedMonthKeys, true))
                            <label class="form-check payment-month-option {{ $isPaidMonth || $isLockedMonth ? 'is-disabled' : '' }}">
                                    <input class="form-check-input month-choice" type="checkbox" name="selected_months[]" value="{{ $monthKey }}" data-month-key="{{ $monthKey }}" {{ $isPaidMonth ? 'checked disabled' : '' }} {{ $isLockedMonth ? 'data-locked=true disabled' : '' }}>
                                    <span class="form-check-label">{{ $khmerMonthNames[$period->month] }} {{ $period->year }}
                                        @if($isPaidMonth)
                                            <small class="month-status text-success"> (បានបង់)</small>
                                        @elseif($isLockedMonth)
                                            <small class="month-status text-muted" data-locked-status> (បានចាក់សោ)</small>
                                        @endif
                                    </span>
                                </label>
                        @empty
                            <div class="text-muted">ជ្រើសរើសសិស្សដែលមានការចុះឈ្មោះ ដើម្បីបង្ហាញខែបង់ប្រាក់</div>
                        @endforelse
                        </div>
                        <div id="month-selection-summary" class="small text-muted mt-2">មិនទាន់បានជ្រើសរើសខែ</div>
                    </div>

                    <div class="payment-month-picker mt-3">
                        <label class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="annual-administrative-fee" {{ $administrativeFeeAlreadyPaid ? 'checked disabled' : '' }}>
                            <span class="form-check-label">បញ្ចូលថ្លៃសេវារដ្ឋបាលប្រចាំឆ្នាំ ($10 / ១២ខែ — $0.83/ខែ){{ $administrativeFeeAlreadyPaid ? ' (បានបង់រួច)' : '' }}</span>
                        </label>
                        <div id="administrative-months-panel" class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="form-label mb-0">ជ្រើសរើសខែសេវារដ្ឋបាល (គិតតម្លៃតាមចំនួនខែ ចាប់ពីខែចុះឈ្មោះ)</span>
                                <div class="form-check administrative-check-all mb-0">
                                    <input class="form-check-input" type="checkbox" id="select-all-administrative-months" {{ $administrativeFeeAlreadyPaid ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="select-all-administrative-months">ជ្រើសរើសទាំងអស់</label>
                                </div>
                            </div>
                            <div class="payment-month-grid administrative-month-grid">
                            @foreach($monthlyPeriods as $period)
                                @php($adminMonthKey = $period->format('Y-m'))
                                @continue(!$administrativeEligibleMonthKeys->contains($adminMonthKey))
                                @php($isPaidAdminMonth = in_array($adminMonthKey, $paidAdminMonthKeys, true))
                                <label class="form-check payment-month-option {{ $isPaidAdminMonth ? 'is-disabled' : '' }}">
                                    <input class="form-check-input admin-month-choice" type="checkbox" name="administrative_months[]" value="{{ $adminMonthKey }}" {{ $isPaidAdminMonth ? 'checked disabled' : '' }}>
                                    <span class="form-check-label">{{ $khmerMonthNames[$period->month] }} {{ $period->year }}@if($isPaidAdminMonth)<small class="text-success"> (បានបង់)</small>@endif</span>
                                </label>
                            @endforeach
                            </div>
                            <div id="administrative-month-summary" class="small text-muted mt-2">មិនទាន់បានជ្រើសរើសខែ — ថ្លៃសេវារដ្ឋបាល: $0.00</div>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6"><label class="form-label">ថ្លៃសិក្សាប្រចាំខែ</label><div class="input-group"><span class="input-group-text">$</span><input class="form-control" value="{{ number_format($monthlyTuitionFee, 2) }}" readonly></div></div>
                        <div class="col-md-6"><label class="form-label">បញ្ចុះតម្លៃលើថ្លៃសិក្សា</label><div class="input-group"><select name="discount_type" id="discount-type" class="form-select"><option value="">-</option><option value="percent">%</option><option value="fixed">$</option></select><input type="number" step="0.01" min="0" name="discount_amount" id="discount-amount" class="form-control" value="{{ old('discount_amount', 0) }}" disabled></div></div>
                    </div>

                    <div class="border rounded p-3 mt-3">
                        <label class="form-label">សេវាកម្មផ្សេងៗ</label>
                        <select name="service_id" id="service-id" class="form-select">
                            <option value="">គ្មាន</option>
                            @foreach($services->reject(fn ($service) => $service->name_kh === 'គ្មាន') as $service)
                                <option value="{{ $service->id }}" data-price="{{ $service->price }}">{{ $service->name_kh }} — ${{ number_format((float) $service->price, 2) }}</option>
                            @endforeach
                        </select>
                        <div class="input-group mt-2"><span class="input-group-text">$</span><input type="number" step="0.01" min="0" name="service_amount" id="service-amount" class="form-control" value="0" placeholder="តម្លៃសេវាកម្ម"></div>
                        <small class="text-muted">តម្លៃសេវាកម្មត្រូវបានទាញពី Database</small>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6"><label class="form-label">អត្រាប្តូរប្រាក់</label><input type="number" step="0.0001" min="0" name="exchange_rate" id="exchange-rate" class="form-control" placeholder="ទទេ = USD"></div>
                        <div class="col-md-6"><label class="form-label">វិធីបង់ប្រាក់</label><select name="payment_method" id="payment-method" class="form-select" required><option value="cash">សាច់ប្រាក់</option><option value="bank">ABA / ACLEDA</option><option value="qr">WING / ACLEDA</option><option value="other">ផ្សេងៗ</option></select></div>
                        <div class="col-md-6"><label class="form-label" for="received-amount">ចំនួនប្រាក់ទទួល</label><div class="input-group"><span class="input-group-text">$</span><input type="number" step="0.01" min="0" name="received_amount" id="received-amount" class="form-control" value="{{ old('received_amount') }}" placeholder="ទទេ = បង់ពេញ"></div></div>
                        <div class="col-12" id="other-bank-wrap" style="display:none"><label class="form-label">ឈ្មោះធនាគារ / អ្នកផ្តល់សេវាទូទាត់</label><input name="other_bank_name" id="other-bank-name" class="form-control"></div>
                    </div>

                    <div class="alert alert-light border mt-3">
                        <div class="d-flex justify-content-between"><span>ថ្លៃសិក្សា</span><span id="tuition-summary">$0.00</span></div>
                        <div class="d-flex justify-content-between"><span>សេវាកម្ម</span><span id="service-summary">$0.00</span></div>
                        <div class="d-flex justify-content-between"><span>ថ្លៃសេវារដ្ឋបាល</span><span id="admin-summary">$0.00</span></div>
                        <div class="d-flex justify-content-between"><span>បញ្ចុះតម្លៃ</span><span id="discount-summary">-$0.00</span></div>
                        <hr><div class="d-flex justify-content-between fw-bold"><span>សរុប</span><span id="total-display">$0.00</span></div>
                        <div class="small text-muted mt-1">ជាអក្សរ៖ <span id="amount-words">សូន្យ ដុល្លារអាមេរិកគត់</span></div>
                    </div>

                    <div class="text-end mt-4"><a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">បោះបង់</a> <button type="submit" class="btn btn-primary">រក្សាទុក</button> <button type="submit" name="save_and_print" value="1" class="btn btn-success">រក្សាទុក និងបោះពុម្ព</button></div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('payment-form');
    function value(id) { return parseFloat(document.getElementById(id)?.value || 0) || 0; }
    function money(amount, exchangeRate) {
        var displayAmount = exchangeRate > 0 ? amount * exchangeRate : amount;
        return (exchangeRate > 0 ? '៛ ' : '$') + displayAmount.toFixed(2);
    }
    function monthInputs() { return Array.from(form.querySelectorAll('.month-choice')); }
    function adminInputs() { return Array.from(form.querySelectorAll('.admin-month-choice')); }
    function displayDate(date) {
        if (!date) return '—';
        var parts = date.split('-');
        return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : date;
    }
    function previewTuition() {
        var preview = document.getElementById('tuition-preview');
        if (!preview) return;
        var startDate = preview.dataset.originalStartDate || preview.dataset.registrationDate;
        var paymentDate = document.getElementById('payment-date').value;
        var selected = monthInputs().filter(function (input) { return input.checked && !input.disabled; }).map(function (input) { return input.value; });
        var monthlyFee = value('monthly-fee');
        var tuition = selected.length * monthlyFee;
        var proratedDays = 0;
        var dailyRate = 0;
        if (startDate && selected.length) {
            var startParts = startDate.split('-');
            var startDay = Number(startParts[2]);
            var firstMonth = selected[0];
            if (startDay >= 15 && firstMonth === startParts[0] + '-' + startParts[1]) {
                var daysInMonth = new Date(Number(startParts[0]), Number(startParts[1]), 0).getDate();
                proratedDays = daysInMonth - startDay + 1;
                dailyRate = Math.round((monthlyFee / daysInMonth) * 100) / 100;
                tuition = dailyRate * proratedDays + Math.max(0, selected.length - 1) * monthlyFee;
            }
            var lastMonth = selected[selected.length - 1].split('-');
            var nextYear = Number(lastMonth[0]);
            var nextMonth = Number(lastMonth[1]) + 1;
            if (nextMonth > 12) { nextMonth = 1; nextYear++; }
            var nextDay = startDay >= 15 ? 1 : Math.min(startDay, new Date(nextYear, nextMonth, 0).getDate());
            var nextDate = nextYear + '-' + String(nextMonth).padStart(2, '0') + '-' + String(nextDay).padStart(2, '0');
            var paidUntilDate = new Date(nextYear, nextMonth - 1, nextDay);
            paidUntilDate.setDate(paidUntilDate.getDate() - 1);
            var paidUntil = paidUntilDate.getFullYear() + '-' + String(paidUntilDate.getMonth() + 1).padStart(2, '0') + '-' + String(paidUntilDate.getDate()).padStart(2, '0');
            document.getElementById('preview-next-payment').textContent = displayDate(nextDate);
            document.getElementById('preview-paid-until').textContent = displayDate(paidUntil);
        } else {
            document.getElementById('preview-next-payment').textContent = '—';
            document.getElementById('preview-paid-until').textContent = preview.dataset.lastPaidUntil ? displayDate(preview.dataset.lastPaidUntil) : '—';
        }
        document.getElementById('preview-registration-date').textContent = displayDate(preview.dataset.registrationDate);
        document.getElementById('preview-original-start-date').textContent = displayDate(startDate);
        document.getElementById('preview-payment-date').textContent = displayDate(paymentDate);
        document.getElementById('preview-selected-months').textContent = selected.length;
        document.getElementById('preview-prorated-days').textContent = proratedDays;
        document.getElementById('preview-daily-rate').textContent = proratedDays > 0 ? '$' + dailyRate.toFixed(2) : '—';
        document.getElementById('preview-tuition').textContent = '$' + tuition.toFixed(2);
    }
    function updateDiscountInput() {
        var discountType = document.getElementById('discount-type');
        var discountAmount = document.getElementById('discount-amount');
        var hasType = Boolean(discountType.value);
        discountAmount.disabled = !hasType;
        if (!hasType) discountAmount.value = '0';
    }
    function updateMonthlySequence() {
        var waitingForMonth = false;
        monthInputs().forEach(function (input) {
            if (input.checked && input.disabled && !input.dataset.locked) return;
            if (input.dataset.locked === 'true' || input.disabled && !input.checked) {
                input.dataset.locked = 'true';
            }
            if (input.disabled && input.checked && !input.dataset.locked) return;
            if (input.disabled && !input.dataset.locked) return;
            if (input.checked) {
                waitingForMonth = false;
                return;
            }
            input.disabled = waitingForMonth;
            var status = input.closest('label')?.querySelector('[data-locked-status]');
            if (status) status.classList.toggle('d-none', !input.disabled);
            input.closest('label')?.classList.toggle('is-disabled', input.disabled);
            if (!input.disabled) waitingForMonth = true;
        });
    }
    function recalc() {
        updateDiscountInput();
        var months = document.querySelectorAll('.month-choice:checked:not(:disabled)').length;
        var tuition = months * value('monthly-fee');
        var annualFeeToggle = document.getElementById('annual-administrative-fee');
        var includeAnnualFee = annualFeeToggle && annualFeeToggle.checked && !annualFeeToggle.disabled;
        var adminMonths = document.querySelectorAll('.admin-month-choice:checked:not(:disabled)').length;
        var service = value('service-amount');
        var admin = includeAnnualFee && adminMonths > 0 ? Math.round((10 * adminMonths / 12) * 100) / 100 : 0;
        var discount = value('discount-amount');
        if (document.getElementById('discount-type').value === 'percent') discount = tuition * discount / 100;
        var total = Math.max(0, tuition - discount + admin + service);
        var receivedAmount = document.getElementById('received-amount');
        if (receivedAmount && !receivedAmount.dataset.edited) receivedAmount.value = total.toFixed(2);
        var exchangeRate = value('exchange-rate');
        var displayTotal = exchangeRate > 0 ? total * exchangeRate : total;
        document.getElementById('tuition-summary').textContent = money(tuition, exchangeRate);
        document.getElementById('service-summary').textContent = money(service, exchangeRate);
        document.getElementById('admin-summary').textContent = money(admin, exchangeRate);
        document.getElementById('discount-summary').textContent = '-' + money(discount, exchangeRate);
        document.getElementById('total-display').textContent = (exchangeRate > 0 ? '៛ ' : '$ ') + displayTotal.toFixed(2);
        document.getElementById('month-selection-summary').textContent = months ? 'បានជ្រើសរើស ' + months + ' ខែ' : 'មិនទាន់បានជ្រើសរើសខែ';
        var adminSummary = document.getElementById('administrative-month-summary');
        if (adminSummary) adminSummary.textContent = adminMonths ? 'បានជ្រើសរើស ' + adminMonths + ' ខែ — ថ្លៃសេវារដ្ឋបាល: ' + money(admin, exchangeRate) : 'មិនទាន់បានជ្រើសរើសខែ — ថ្លៃសេវារដ្ឋបាល: ' + money(0, exchangeRate);
        updateAdministrativeState();
        previewTuition();
    }
    function updateAdministrativeState() {
        var toggle = document.getElementById('annual-administrative-fee');
        var panel = document.getElementById('administrative-months-panel');
        var includeAnnualFee = toggle && toggle.checked && !toggle.disabled;
            if (panel) panel.classList.toggle('opacity-50', !includeAnnualFee && !(toggle && toggle.disabled));
        adminInputs().forEach(function (input) {
            if (!input.dataset.paid) input.disabled = !includeAnnualFee;
        });
        syncAdministrativeCheckAll();
    }
    function syncAdministrativeCheckAll() {
        var selectAll = document.getElementById('select-all-administrative-months');
        if (selectAll) {
            var toggle = document.getElementById('annual-administrative-fee');
            var includeAnnualFee = toggle && toggle.checked && !toggle.disabled;
            var selected = adminInputs().filter(function (input) { return input.checked; });
            selectAll.disabled = toggle && toggle.disabled;
            selectAll.checked = adminInputs().length > 0 && adminInputs().length === selected.length;
        }
    }
    var annualAdministrativeFee = document.getElementById('annual-administrative-fee');
    if (annualAdministrativeFee) annualAdministrativeFee.addEventListener('change', function () {
        if (!annualAdministrativeFee.checked) adminInputs().forEach(function (input) { if (!input.dataset.paid) input.checked = false; });
        updateAdministrativeState();
        recalc();
    });
    var selectAllAdministrativeMonths = document.getElementById('select-all-administrative-months');
    function applyAdministrativeSelectAll() {
        var shouldSelect = selectAllAdministrativeMonths.checked;
        if (selectAllAdministrativeMonths.checked && annualAdministrativeFee && !annualAdministrativeFee.checked && !annualAdministrativeFee.disabled) {
            annualAdministrativeFee.checked = true;
            updateAdministrativeState();
        }
        adminInputs().filter(function (input) { return !input.disabled; }).forEach(function (input) {
            input.checked = shouldSelect;
        });
        selectAllAdministrativeMonths.checked = shouldSelect;
        syncAdministrativeCheckAll();
        recalc();
    }
    if (selectAllAdministrativeMonths) {
        selectAllAdministrativeMonths.addEventListener('change', applyAdministrativeSelectAll);
        var selectAllLabel = document.querySelector('.administrative-check-all .form-check-label');
        if (selectAllLabel) selectAllLabel.addEventListener('click', function (event) {
            event.preventDefault();
            selectAllAdministrativeMonths.checked = !selectAllAdministrativeMonths.checked;
            applyAdministrativeSelectAll();
        });
    }
    form.querySelectorAll('input, select').forEach(function (input) { input.addEventListener('input', recalc); input.addEventListener('change', recalc); });
    var receivedAmount = document.getElementById('received-amount');
    if (receivedAmount) receivedAmount.addEventListener('input', function () { receivedAmount.dataset.edited = 'true'; });
    form.querySelectorAll('.month-choice').forEach(function (input) {
        if (input.checked && input.disabled) input.dataset.paid = 'true';
        input.addEventListener('change', function () { updateMonthlySequence(); recalc(); });
    });
    form.querySelectorAll('.admin-month-choice').forEach(function (input) {
        if (input.checked && input.disabled) input.dataset.paid = 'true';
        input.addEventListener('change', function () { syncAdministrativeCheckAll(); recalc(); });
    });
    form.addEventListener('submit', function (event) {
        if (!form.querySelector('.month-choice:checked:not(:disabled)')) {
            event.preventDefault();
            window.alert('សូមជ្រើសរើសខែដែលត្រូវបង់ប្រាក់។');
        }
        if (annualAdministrativeFee && annualAdministrativeFee.checked && !annualAdministrativeFee.disabled && !form.querySelector('.admin-month-choice:checked:not(:disabled)')) {
            event.preventDefault();
            window.alert('សូមជ្រើសរើសខែសម្រាប់ថ្លៃសេវារដ្ឋបាល។');
        }
    });
    document.getElementById('service-id').addEventListener('change', function () { var price = this.selectedOptions[0]?.dataset.price || 0; var amount = document.getElementById('service-amount'); amount.value = this.value ? price : '0'; amount.disabled = !this.value; recalc(); });
    document.getElementById('payment-method').addEventListener('change', function () { var other = this.value === 'other'; document.getElementById('other-bank-wrap').style.display = other ? '' : 'none'; var input = document.getElementById('other-bank-name'); input.disabled = !other; if (!other) input.value = ''; });
    document.getElementById('service-amount').disabled = true;
    document.getElementById('other-bank-name').disabled = true;
    updateMonthlySequence();
    updateAdministrativeState();
    recalc();
})();
</script>
@endpush
@endsection
