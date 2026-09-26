@extends('layouts.app')

@section('title', 'កែប្រែការបង់ប្រាក់')
@section('page-title', 'កែប្រែការបង់ប្រាក់')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-pen text-primary me-2"></i>កែប្រែការបង់ប្រាក់</h2>
        <p class="page-header-subtitle">{{ $payment->reference }} · {{ $payment->student?->name_kh }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('payments.update', $payment) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">លេខវិក្កយបត្រ</label><input class="form-control" value="{{ $payment->invoice?->invoice_number ?? $payment->reference }}" readonly></div>
                <div class="col-md-4"><label class="form-label">សិស្ស</label><input class="form-control" value="{{ $payment->student?->name_kh }}" readonly></div>
                <div class="col-md-4"><label class="form-label">ចំនួនបច្ចុប្បន្ន</label><input class="form-control" value="{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}" readonly></div>
                <div class="col-md-4"><label class="form-label" for="payment-date">កាលបរិច្ឆេទបង់ប្រាក់ *</label><input type="date" id="payment-date" name="payment_date" class="form-control" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}" required></div>
                <div class="col-md-4"><label class="form-label" for="paid-until">បានបង់រហូតដល់</label><input type="date" id="paid-until" name="paid_until" class="form-control" value="{{ old('paid_until', $payment->paid_until?->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label" for="next-payment-date">ថ្ងៃបង់បន្ទាប់</label><input type="date" id="next-payment-date" name="next_payment_date" class="form-control" value="{{ old('next_payment_date', $payment->next_payment_date?->format('Y-m-d')) }}"></div>
                <div class="col-md-4"><label class="form-label" for="discount-type">ប្រភេទបញ្ចុះតម្លៃ</label><select id="discount-type" name="discount_type" class="form-select"><option value="">គ្មាន</option><option value="percent" @selected(old('discount_type', $payment->discount_type) === 'percent')>%</option><option value="fixed" @selected(old('discount_type', $payment->discount_type) === 'fixed')>$</option></select></div>
                <div class="col-md-4"><label class="form-label" for="discount-amount">ចំនួនបញ្ចុះតម្លៃ</label><input type="number" step="0.01" min="0" id="discount-amount" name="discount_amount" class="form-control" value="{{ old('discount_amount', $payment->discount_amount) }}"></div>
                <div class="col-md-4"><label class="form-label" for="payment-method">វិធីបង់ប្រាក់</label><select id="payment-method" name="payment_method" class="form-select" required><option value="cash" @selected(old('payment_method', $payment->payment_method) === 'cash')>សាច់ប្រាក់</option><option value="bank" @selected(old('payment_method', $payment->payment_method) === 'bank')>ABA / ACLEDA</option><option value="qr" @selected(old('payment_method', $payment->payment_method) === 'qr')>WING / QR</option><option value="other" @selected(old('payment_method', $payment->payment_method) === 'other')>ផ្សេងៗ</option></select></div>
                <div class="col-12"><label class="form-label" for="notes">បរិយាយ</label><textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $payment->notes) }}</textarea></div>
            </div>

            <div class="alert alert-light border mt-4">
                <strong>ខែដែលបានបង់:</strong> {{ $payment->paymentMonths->pluck('month_key')->join(', ') ?: '—' }}
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-secondary">បោះបង់</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>រក្សាទុកការកែប្រែ</button>
            </div>
        </form>
    </div>
</div>
@endsection