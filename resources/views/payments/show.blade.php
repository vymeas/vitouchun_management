@extends('layouts.app')

@section('title', 'វិក្កយបត្រ')
@section('page-title', 'ព័ត៌មានការបង់ប្រាក់')

@section('content')
<div class="invoice-toolbar print-hide">
    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">ត្រឡប់ទៅប្រវត្តិបង់ប្រាក់</a>
    <div class="d-flex gap-2"><a href="{{ route('payments.create', ['student_id' => $payment->student_id, 'enrollment_id' => $payment->enrollment_id, 'invoice_id' => $payment->invoiceDocument?->id ?? $payment->invoice?->id]) }}" class="btn btn-outline-success">បង់បន្ថែម</a><a href="{{ route('payments.refund.create', $payment) }}" class="btn btn-outline-danger">សងប្រាក់</a><a href="{{ route('payments.receipt', $payment) }}" class="btn btn-outline-primary">បោះពុម្ពបង្កាន់ដៃ</a><button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i> បោះពុម្ពវិក្កយបត្រ</button></div>
</div>
<div class="alert alert-light border print-hide mb-3">
    <div class="row g-2 small">
        <div class="col-md-2"><strong>ត្រូវបង់:</strong><br>{{ $payment->currency }} {{ number_format($settlement['due'], 2) }}</div>
        <div class="col-md-2"><strong>បានបង់:</strong><br>{{ $payment->currency }} {{ number_format($settlement['paid'], 2) }}</div>
        <div class="col-md-2"><strong>បានសង:</strong><br>{{ $payment->currency }} {{ number_format($settlement['refunded'], 2) }}</div>
        <div class="col-md-2"><strong>ឥណទាន:</strong><br>{{ $payment->currency }} {{ number_format($credit, 2) }}</div>
        <div class="col-md-2"><strong>នៅជំពាក់:</strong><br>{{ $payment->currency }} {{ number_format($settlement['outstanding'], 2) }}</div>
        <div class="col-md-2"><strong>ស្ថានភាព:</strong><br>{{ ['paid' => 'បានបង់គ្រប់', 'partial' => 'បង់មិនទាន់គ្រប់', 'overpaid' => 'បង់លើស'][$settlement['status']] ?? $settlement['status'] }}</div>
    </div>
</div>

<main class="invoice-print-sheet">
    @include('payments._invoice-copy', ['copyLabel' => ''])
    @include('payments._invoice-copy', ['copyLabel' => ''])
</main>

@if($payment->refunds->isNotEmpty())
<div class="card mt-3 print-hide" style="max-width: 297mm; margin-left: auto; margin-right: auto;">
    <div class="card-header">ប្រវត្តិការសងប្រាក់</div>
    <div class="table-responsive"><table class="table table-sm mb-0"><thead><tr><th>លេខសងប្រាក់</th><th>កាលបរិច្ឆេទ</th><th>មូលហេតុ</th><th class="text-end">ចំនួន</th></tr></thead><tbody>@foreach($payment->refunds as $refund)<tr><td>{{ $refund->refund_no }}</td><td>{{ $refund->refund_date?->format('d/m/Y') }}</td><td>{{ $refund->reason ?: '—' }}</td><td class="text-end">-{{ $payment->currency }} {{ number_format($refund->amount, 2) }}</td></tr>@endforeach</tbody></table></div>
</div>
@endif

@push('styles')
<style>
    .invoice-toolbar {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        max-width: 297mm;
        margin: 0 auto 1rem;
    }

    .invoice-print-sheet {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        width: min(100%, 297mm);
        min-height: 210mm;
        margin: 0 auto;
        background: #fff;
        color: #172b4d;
        font-family: 'Noto Sans Khmer', 'Khmer OS Battambang', sans-serif;
        border: 1px solid #cbd5e1;
    }

    .invoice-copy {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 210mm;
        padding: 7mm 8mm 5mm;
        background: #fff;
    }

    .invoice-copy + .invoice-copy {
        border-left: 1px dashed #64748b;
    }

    .invoice-header {
        padding-bottom: 3mm;
        text-align: center;
        border-bottom: 2px solid #1e3a5f;
    }

    .invoice-logo,
    .invoice-logo-placeholder {
        display: block;
        width: 18mm;
        height: 18mm;
        margin: 0 auto 1.5mm;
        object-fit: contain;
    }

    .invoice-logo-placeholder {
        display: grid;
        place-items: center;
        color: #1e3a5f;
        font-size: 13mm;
    }

    .invoice-school-name {
        color: #102a43;
        font-family: 'Khmer OS Moul Light', 'Noto Sans Khmer', sans-serif;
        font-size: 5mm;
        font-weight: 700;
        line-height: 1.25;
    }

    .invoice-tagline {
        margin-top: 1mm;
        color: #52606d;
        font-size: 2.5mm;
    }

    .invoice-copy-label {
        align-self: flex-end;
        margin-top: 2mm;
        color: #52606d;
        font-size: 2.3mm;
    }

    .invoice-title {
        margin: 1mm 0 3mm;
        color: #102a43;
        font-family: 'Khmer OS Moul Light', 'Noto Sans Khmer', sans-serif;
        font-size: 4.5mm;
        font-weight: 700;
        text-align: center;
    }

    .invoice-section {
        margin-bottom: 3mm;
    }

    .invoice-section h2 {
        margin: 0;
        padding: 1.5mm 2.5mm;
        color: #fff;
        background: #1e3a5f;
        font-size: 3mm;
        font-weight: 700;
    }

    .invoice-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.5mm 4mm;
        padding: 2.5mm;
        border: 1px solid #cbd5e1;
        border-top: 0;
        font-size: 2.35mm;
        line-height: 1.45;
    }

    .invoice-info-grid strong {
        color: #334e68;
        font-weight: 700;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #9fb3c8;
        font-size: 2.25mm;
    }

    .invoice-table th,
    .invoice-table td {
        padding: 1.3mm 1.5mm;
        border: 1px solid #cbd5e1;
        vertical-align: middle;
    }

    .invoice-table thead th {
        color: #173f5f;
        background: #d9eaf7;
        font-weight: 700;
        text-align: left;
    }

    .invoice-table .number-column { width: 9%; text-align: center; }
    .invoice-table .amount-column { width: 25%; }
    .invoice-table small { color: #52606d; font-size: 1.9mm; }
    .invoice-table tfoot td { font-weight: 700; background: #f0f4f8; }

    .payment-info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .invoice-signatures {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 4mm;
        margin-top: auto;
        padding-top: 4mm;
        font-size: 2.3mm;
        text-align: center;
    }

    .invoice-signatures div { display: grid; grid-template-rows: auto auto 9mm auto; align-items: end; min-height: 23mm; }
    .invoice-signatures i { display: block; width: 100%; border-bottom: 1px solid #52606d; }
    .invoice-signatures small { color: #52606d; font-size: 1.9mm; }
    .invoice-signatures strong { color: #102a43; font-size: 2.2mm; }

    .invoice-footer {
        margin-top: 4mm;
        color: #52606d;
        font-size: 2mm;
        text-align: center;
    }

    .invoice-footer strong {
        display: block;
        color: #102a43;
        font-size: 2.8mm;
    }

    .invoice-footer div {
        height: 1px;
        margin: 1.5mm 0;
        background: #b7791f;
    }

    @media (max-width: 900px) {
        .invoice-print-sheet { width: 100%; }
        .invoice-copy { padding: 1rem; }
        .invoice-school-name { font-size: 1.1rem; }
        .invoice-tagline, .invoice-copy-label, .invoice-info-grid, .invoice-table, .invoice-footer { font-size: .7rem; }
        .invoice-section h2, .invoice-title { font-size: .85rem; }
    }

    @page { size: A4 landscape; margin: 0; }

    @media print {
        html, body { width: 297mm; height: 210mm; margin: 0 !important; }
        body { background: #fff !important; }
        body * { visibility: hidden; }
        .invoice-print-sheet,
        .invoice-print-sheet * { visibility: visible; }
        .invoice-print-sheet {
            position: absolute;
            inset: 0;
            display: grid;
            width: 297mm;
            height: 210mm;
            min-height: 210mm;
            margin: 0;
            border: 0;
        }
        .invoice-copy {
            width: 148.5mm;
            height: 210mm;
            min-height: 210mm;
            padding: 7mm 8mm 5mm;
            box-sizing: border-box;
            break-inside: avoid;
        }
        .invoice-print-sheet,
        .invoice-copy,
        .invoice-section,
        .invoice-table { break-inside: avoid; page-break-inside: avoid; }
        .invoice-table tr { break-inside: avoid; page-break-inside: avoid; }
        .invoice-table tfoot { display: table-footer-group;
        }
        .print-hide { display: none !important; }
    }
</style>
@endpush

@if(request('print'))
    @push('scripts')
        <script>window.addEventListener('load', function () { window.print(); });</script>
    @endpush
@endif
@endsection