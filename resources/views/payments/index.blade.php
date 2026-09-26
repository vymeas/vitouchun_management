@extends('layouts.app')

@section('title', 'ប្រវត្តិបង់ប្រាក់')
@section('page-title', 'ប្រវត្តិបង់ប្រាក់')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-money-bill-wave text-primary me-2"></i>ប្រវត្តិបង់ប្រាក់</h2>
        <p class="page-header-subtitle">{{ $payments->total() }} សិស្ស</p>
    </div>
    <a href="{{ route('payments.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>ទទួលប្រាក់</a>
</div>

<form class="filter-bar mb-3 row g-2 align-items-end">
    <div class="col-12 col-xl-3"><input name="search" class="form-control" placeholder="REF ឬញ្មោះសិស្ស..." value="{{ request('search') }}"></div>
    <div class="col-6 col-xl-2">
        <select name="payment_status" class="form-select">
            <option value="">ស្ថានភាពបង់ប្រាក់: ទាំងអស់</option>
            <option value="paid" {{ request('payment_status')==='paid'?'selected':'' }}>សិស្សបង់គ្រប់</option>
            <option value="owing" {{ request('payment_status')==='owing'?'selected':'' }}>សិស្សជំពាក់</option>
        </select>
    </div>
    <div class="col-6 col-xl-2">
        <select name="academic_year_id" class="form-select">
            <option value="">ឆ្នាំសិក្សា: ទាំងអស់</option>
            @foreach($academicYears as $academicYear)
                <option value="{{ $academicYear->id }}" {{ (string) request('academic_year_id') === (string) $academicYear->id ? 'selected' : '' }}>{{ $academicYear->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-6 col-xl-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
    <div class="col-6 col-xl-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
    <div class="col-12 col-xl-auto d-flex gap-2"><button class="btn btn-primary"><i class="fas fa-search"></i>ស្វែងរក</button><a href="{{ route('payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div>
</form>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 table-fluid">
            <colgroup>
                <col style="width: 15%">
                <col style="width: 9%">
                <col style="width: 11%">
                <col style="width: 9%">
                <col style="width: 7%">
                <col style="width: 11%">
                <col style="width: 7%">
                <col style="width: 9%">
                <col style="width: 9%">
                <col style="width: 13%">
            </colgroup>
            <thead>
                <tr>
                    <th>សិស្ស</th><th>ថ្ងៃបង់ប្រាក់</th><th>លេខវិក្ក័យបត្រ</th><th>ថ្នាក់</th><th>រយៈពេល</th><th>ចំនួនទឹកប្រាក់</th><th>វិធីសាស្ត្រ</th><th>ថ្ងៃបង់បន្ទាប់</th><th>ស្ថានភាព</th><th>សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $student)
                    @php
                        $payment = $student->latestPayment;
                        $tuitionMonthCount = $payment->paymentMonths->where('type', 'tuition')->count();
                        $paymentMethod = match($payment->payment_method) { 'cash' => 'សាច់ប្រាក់', 'bank' => 'ធនាគារ', 'qr' => 'QR', default => 'ផ្សេងៗ' };
                        $isOwing = (bool) $payment->is_owing;
                    @endphp
                    <tr>
                        <td class="fw-medium">{{ $student->khmer_name ?? $student->name_kh ?? '—' }}<div class="small text-muted">{{ $student->student_code ?? $student->code }}</div></td>
                        <td>{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="font-monospace">{{ $payment->invoice?->invoice_number ?? $payment->reference }}</td>
                        <td>{{ $payment->enrollment?->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $tuitionMonthCount ? $tuitionMonthCount . ' ខែ' : '—' }}</td>
                        <td class="fw-bold">{{ $payment->currency }} {{ number_format($payment->total_amount, 2) }}</td>
                        <td>{{ $paymentMethod }}</td>
                        <td>{{ $payment->next_payment_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if($isOwing)
                                <span class="badge text-bg-warning">សិស្សជំពាក់</span>
                            @else
                                <span class="badge text-bg-success">សិស្សបង់គ្រប់</span>
                            @endif
                        </td>
                        <td><div class="d-flex flex-nowrap gap-1 action-btn-group">
                            <a href="{{ route('payments.history', $student) }}" class="btn btn-sm btn-outline-secondary" title="មើលប្រវត្តិបង់ប្រាក់"><i class="fas fa-eye"></i></a>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('invoice.view'))<a href="{{ route('payments.invoice', $payment) }}" class="btn btn-sm btn-primary" title="មើលវិក្កយបត្រ"><i class="fas fa-file-invoice"></i></a>@endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('payment.edit'))<a href="{{ route('payments.edit', $payment) }}" class="btn btn-sm btn-warning" title="កែប្រែ"><i class="fas fa-edit"></i></a>@endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('payment.delete'))<form method="POST" action="{{ route('payments.destroy', $payment) }}" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបការបង់ប្រាក់នេះមែនទេ?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger" title="លុប"><i class="fas fa-trash"></i></button></form>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="10"><div class="empty-state"><i class="fas fa-money-bill-wave"></i><h5>មិនមានការបង់ប្រាក់</h5></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $payments->links() }}</div>
</div>
@endsection
