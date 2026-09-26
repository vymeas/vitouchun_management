@extends('layouts.app')
@section('title', 'របាយការណ៍ ' . ucfirst($type))
@section('page-title', 'របាយការណ៍ ' . ucfirst($type))
@section('content')
<div class="page-header"><div><h2 class="page-header-title"><i class="fas fa-file-lines text-primary me-2"></i>របាយការណ៍ {{ ucfirst($type) }}</h2><p class="page-header-subtitle">ទិន្នន័យត្រូវបានទាញពី Database</p></div><div class="d-flex gap-2"><a href="{{ route('reports.export', array_merge(['type'=>$type], request()->query())) }}" class="btn btn-outline-success"><i class="fas fa-file-csv"></i>CSV</a><a href="{{ route('reports.pdf', array_merge(['type'=>$type], request()->query())) }}" class="btn btn-outline-danger"><i class="fas fa-file-pdf"></i>PDF</a><button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print"></i>បោះពុម្ព</button></div></div>
@if($type === 'payments')
<div class="d-flex flex-wrap gap-2 mb-3">
    @php($currentView = request('view', 'transactions'))
    @foreach(['transactions' => 'ប្រតិបត្តិការណ៍', 'income' => 'ចំណូល', 'expired' => 'សិស្សផុតកំណត់', 'owing' => 'សិស្សជំពាក់'] as $viewKey => $viewLabel)
        <a href="{{ route('reports.show', array_merge(['type' => $type], request()->except('view'), ['view' => $viewKey])) }}" class="btn btn-sm {{ $currentView === $viewKey ? 'btn-primary' : 'btn-outline-primary' }}">{{ $viewLabel }}</a>
    @endforeach
</div>
@endif
<form method="GET" class="filter-bar mb-3 row g-2">@if($type === 'payments')<input type="hidden" name="view" value="{{ request('view', 'transactions') }}">@endif<div class="col-md-4"><input name="search" class="form-control" placeholder="ស្វែងរក..." value="{{ request('search') }}"></div><div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div><div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div><div class="col-md-2"><select name="status" class="form-select"><option value="">សភាពទាំងអស់</option><option value="active" {{ request('status')==='active'?'selected':'' }}>សកម្ម</option><option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>អសកម្ម</option><option value="absent" {{ request('status')==='absent'?'selected':'' }}>អវត្តមាន</option></select></div><div class="col-auto"><button class="btn btn-primary"><i class="fas fa-filter"></i>តម្រង</button><a href="{{ route('reports.show',$type) }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div></form><div class="row g-2 mb-3">@foreach($summary as $key=>$value)<div class="col-auto"><div class="badge bg-light text-dark border p-2">{{ ucfirst($key) }}: {{ is_numeric($value) ? number_format($value,2) : $value }}</div></div>@endforeach</div>
@if($type === 'payments' && ($currentView ?? request('view','transactions')) === 'transactions' && isset($records))
<div class="card table-card">
    <div class="table-container">
        <table class="table table-hover align-middle mb-0 table-fluid">
            <colgroup>
                <col style="width: 4%"><col style="width: 10%"><col style="width: 11%"><col style="width: 15%"><col style="width: 9%"><col style="width: 9%"><col style="width: 11%"><col style="width: 11%"><col style="width: 10%"><col style="width: 12%"><col style="width: 8%">
            </colgroup>
            <thead>
                <tr><th>#</th><th>ថ្ងៃបង់ប្រាក់</th><th>លេខវិក្កយបត្រ</th><th>ឈ្មោះសិស្ស</th><th>ថ្នាក់</th><th>វេនសិក្សា</th><th>ចំនួនទឹកប្រាក់</th><th>វិធីសាស្រ្ត</th><th>អ្នកប្រើប្រាស់</th><th>បរិយាយ</th><th class="text-end">វិក្កយបត្រ</th></tr>
            </thead>
            <tbody>
                @forelse($records as $payment)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>{{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="font-monospace">{{ $payment->invoice?->invoice_number ?? $payment->reference }}</td>
                        <td>{{ $payment->student?->name_kh ?? '—' }}</td>
                        <td>{{ $payment->enrollment?->schoolClass?->name ?? '—' }}</td>
                        <td>{{ $payment->enrollment?->schoolClass?->shift?->name ?? '—' }}</td>
                        <td class="fw-bold">{{ $payment->currency === 'KHR' ? '៛' . number_format((float) $payment->total_amount, 0) : '$' . number_format((float) $payment->total_amount, 2) }}</td>
                        <td>{{ match($payment->payment_method) { 'cash' => 'សាច់ប្រាក់', 'bank' => 'ធនាគារ (ABA/ACLEDA)', 'qr' => 'QR (WING/ACLEDA)', 'other' => $payment->other_bank_name ?: 'ផ្សេងៗ', default => 'ផ្សេងៗ' } }}</td>
                        <td>{{ $payment->creator?->display_name ?? '—' }}</td>
                        <td>{{ $payment->notes ?: '-' }}</td>
                        <td class="text-end">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->can('invoice.view'))
                                <a href="{{ route('payments.invoice', $payment) }}" class="btn btn-sm btn-primary" title="មើលវិក្កយបត្រ"><i class="fas fa-file-invoice"></i></a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11"><div class="empty-state"><i class="fas fa-file-circle-xmark"></i><h5>រកមិនឃើញទិន្នន័យតាមលក្ខខណ្ឌ</h5></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card table-card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr>@foreach($columns as $column)<th>{{ $column }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr>@foreach($row as $value)<td>{{ $value ?? '—' }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($columns) }}"><div class="empty-state"><i class="fas fa-file-circle-xmark"></i><h5>រកមិនឃើញទិន្នន័យតាមលក្ខខណ្ឌ</h5></div></td></tr>@endforelse</tbody></table></div></div>
@endif
@endsection
