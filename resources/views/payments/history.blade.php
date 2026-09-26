@extends('layouts.app')

@section('title', 'ប្រវត្តិបង់ប្រាក់សិស្ស')
@section('page-title', 'ប្រវត្តិបង់ប្រាក់សិស្ស')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-history text-primary me-2"></i>ប្រវត្តិបង់ប្រាក់ — {{ $student->khmer_name ?? $student->name_kh }}</h2>
        <p class="page-header-subtitle">{{ $paymentHistory->count() }} ការបង់ប្រាក់</p>
    </div>
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i>ត្រឡប់</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4"><strong>ឈ្មោះសិស្ស:</strong><br>{{ $student->khmer_name ?? $student->name_kh }}</div>
            <div class="col-md-2"><strong>Student ID:</strong><br>{{ $student->student_code ?? $student->code }}</div>
            <div class="col-md-2"><strong>ថ្នាក់:</strong><br>{{ $activeEnrollment?->schoolClass?->name ?? '—' }}</div>
            <div class="col-md-2"><strong>វេនសិក្សា:</strong><br>{{ $activeEnrollment?->schoolClass?->shift?->name ?? '—' }}</div>
            <div class="col-md-2"><strong>សាខា:</strong><br>{{ $student->branch?->name_kh ?: ($student->branch?->name ?? '—') }}</div>
            <div class="col-md-2">
                <strong>ស្ថានភាព:</strong><br>
                @switch($activeEnrollment?->status ?? $student->status)
                    @case('active')
                        <span class="badge badge-active">កំពុងសិក្សា</span>
                        @break
                    @case('pending')
                        <span class="badge bg-warning text-dark">រង់ចាំ</span>
                        @break
                    @case('completed')
                        <span class="badge bg-secondary">បញ្ចប់</span>
                        @break
                    @case('transferred')
                        <span class="badge bg-info text-dark">ផ្ទេរ</span>
                        @break
                    @default
                        <span class="badge badge-inactive">អសកម្ម</span>
                @endswitch
            </div>
        </div>
    </div>
</div>

<div class="card table-card">
    <div class="table-container">
        <table class="table table-hover align-middle mb-0 table-fluid">
            <colgroup>
                <col style="width: 4%"><col style="width: 10%"><col style="width: 11%"><col style="width: 16%"><col style="width: 10%"><col style="width: 11%"><col style="width: 11%"><col style="width: 10%"><col style="width: 9%">
            </colgroup>
            <thead>
                <tr><th>#</th><th>ថ្ងៃបង់ប្រាក់</th><th>លេខវិក្ក័យបត្រ</th><th>បរិយាយ</th><th>ចំនួនទឹកប្រាក់</th><th>វិធីសាស្រ្ត</th><th>អ្នកប្រើប្រាស់</th><th>ស្ថានភាពបង់ប្រាក់</th><th class="text-end">វិក្កយបត្រ</th></tr>
            </thead>
            <tbody>
                @forelse($paymentHistory as $payment)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="font-monospace">{{ $payment->invoice?->invoice_number ?? $payment->reference }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                        <td class="fw-bold">{{ $payment->currency === 'KHR' ? '៛' . number_format((float) $payment->total_amount, 0) : '$' . number_format((float) $payment->total_amount, 2) }}</td>
                        <td>{{ match($payment->payment_method) { 'cash' => 'សាច់ប្រាក់', 'bank' => 'ធនាគារ', 'qr' => 'QR', default => 'ផ្សេងៗ' } }}</td>
                        <td>{{ $payment->creator?->display_name ?? '—' }}</td>
                        <td>
                            @if($payment->is_owing)
                                <span class="badge text-bg-warning">សិស្សជំពាក់</span>
                            @else
                                <span class="badge text-bg-success">សិស្សបង់គ្រប់</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('invoice.view'))
                                <a href="{{ route('payments.invoice', $payment) }}" class="btn btn-sm btn-primary" title="មើលវិក្កយបត្រ"><i class="fas fa-file-invoice"></i></a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9"><div class="empty-state"><i class="fas fa-money-bill-wave"></i><h5>មិនមានការបង់ប្រាក់</h5></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
