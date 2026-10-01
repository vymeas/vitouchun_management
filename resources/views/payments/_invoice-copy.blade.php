@php
    $schoolName = \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន');
    $tagline = \App\Services\SettingService::get('school_tagline', 'សុខភាព វិន័យ ចំណេះដឹង  បំណិន សីលធម៌ គុណធម៌');
    $currency = $payment->currency ?: 'USD';
    $exchangeRate = (float) $payment->exchange_rate;
    $toUsd = fn ($amount) => $currency === 'KHR' && $exchangeRate > 0 ? (float) $amount / $exchangeRate : (float) $amount;
    $usdTotal = $toUsd($payment->total_amount);
    $invoiceDocument = $payment->invoiceDocument ?: $payment->invoice;
    $settlementSummary = $settlement ?? null;
    $originalStartDate = $payment->enrollment?->enrollment_date ?? $payment->payment_date;
    $paymentMonths = $payment->paymentMonths;
    $tuitionMonths = $paymentMonths->where('type', 'tuition');
    $administrativeMonths = $paymentMonths->where('type', 'administrative');
    $tuitionItems = $payment->items->filter(fn ($item) => str_starts_with((string) $item->description, 'Tuition'));
    $administrativeItem = $payment->items->first(fn ($item) => str_contains(strtolower((string) $item->description), 'administrative'));
    $serviceItems = $payment->items->reject(fn ($item) => str_starts_with((string) $item->description, 'Tuition') || str_contains(strtolower((string) $item->description), 'administrative'));
    $gender = match (strtoupper((string) $payment->student?->gender)) {
        'F', 'FEMALE' => 'ស្រី',
        'M', 'MALE' => 'ប្រុស',
        default => '—',
    };
    $receiverName = $payment->creator?->display_name ?? $payment->creator?->username ?? '—';
    $tuitionDuration = $tuitionMonths->count() . ' ខែ';
    if ($originalStartDate && $originalStartDate->day >= 15 && $tuitionMonths->contains('month_key', $originalStartDate->format('Y-m'))) {
        // Prorated days come straight from the registration date's calendar (handles 28/29/30/31-day months exactly), not back-solved from money.
        $proratedDays = $originalStartDate->daysInMonth - $originalStartDate->day + 1;
        $fullMonthsCount = max(0, $tuitionMonths->count() - 1);
        $tuitionDuration = $fullMonthsCount > 0 ? $fullMonthsCount . ' ខែ ' . $proratedDays . ' ថ្ងៃ' : $proratedDays . ' ថ្ងៃ';
    }
@endphp

<section class="invoice-copy">
    <header class="invoice-header">
        <img class="invoice-logo" src="{{ asset('storage/images/logo.png') }}" alt="{{ $schoolName }}">
        <div class="invoice-school-name">{{ $schoolName }}</div>
        <div class="invoice-tagline">{{ $tagline }}</div>
    </header>

    <div class="invoice-copy-label">{{ $copyLabel }}</div>
    <h1 class="invoice-title">វិក្កយបត្រទទួលប្រាក់</h1>

    <section class="invoice-section">
        <h2>ព័ត៌មានសិស្ស</h2>
        <div class="invoice-info-grid">
            <div><strong>លេខវិក្កយបត្រ:</strong> {{ $invoiceDocument?->invoice_number ?? $payment->reference }}</div>
            <div><strong>កាលបរិច្ឆេទបង់ប្រាក់:</strong> {{ $payment->payment_date?->format('d/m/Y') ?? '—' }}</div>
            <div><strong>ឈ្មោះ-នាមសិស្ស:</strong> {{ $payment->student?->name_kh ?? '—' }}</div>
            <div><strong>ភេទ:</strong> {{ $gender }}</div>
            <div><strong>ថ្នាក់:</strong> {{ $payment->enrollment?->schoolClass?->name ?? '—' }}</div>
            <div><strong>ឆ្នាំសិក្សា:</strong> {{ $payment->enrollment?->academicYear?->name ?? '—' }}</div>
            <div><strong>លេខទូរស័ព្ទ:</strong> {{ $payment->student?->phone ?? $payment->student?->parent_phone ?? '—' }}</div>
            <div><strong>វេនសិក្សា:</strong> {{ $payment->enrollment?->schoolClass?->shift?->name ?? '—' }}</div>
        </div>
    </section>

    <section class="invoice-section">
        <h2>ព័ត៌មានការបង់ប្រាក់</h2>
        <table class="invoice-table">
            <thead>
                <tr><th class="number-column">ល.រ</th><th>បរិយាយ</th><th>រយៈពេល</th><th class="amount-column">ចំនួនទឹកប្រាក់</th></tr>
            </thead>
            <tbody>
                @if($tuitionItems->isNotEmpty())
                    <tr>
                        <td class="text-center">1</td>
                        <td>ថ្លៃសិក្សា</td>
                        <td>{{ $tuitionDuration }}</td>
                        <td class="text-end">$ {{ number_format($toUsd($tuitionItems->sum('amount')), 2) }}</td>
                    </tr>
                @endif
                @if($administrativeItem)
                    <tr>
                        <td class="text-center">{{ $tuitionItems->isNotEmpty() ? 2 : 1 }}</td>
                        <td>ថ្លៃសេវារដ្ឋបាល</td>
                        <td>{{ $administrativeMonths->count() }} ខែ</td>
                        <td class="text-end">$ {{ number_format($toUsd($administrativeItem->amount), 2) }}</td>
                    </tr>
                @endif
                @foreach($serviceItems as $item)
                    <tr>
                        <td class="text-center">{{ $loop->iteration + ($tuitionItems->isNotEmpty() ? 1 : 0) + ($administrativeItem ? 1 : 0) }}</td>
                        <td>{{ $item->description }}</td>
                        <td>—</td>
                        <td class="text-end">$ {{ number_format($toUsd($item->amount), 2) }}</td>
                    </tr>
                @endforeach
                @if((float) $payment->discount_amount > 0)
                    <tr>
                        <td></td><td>បញ្ចុះតម្លៃ</td><td>—</td>
                        <td class="text-end">-$ {{ number_format($toUsd($payment->discount_amount), 2) }}</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr><td colspan="3" class="text-end">សរុបសុទ្ធ (USD)</td><td class="text-end">$ {{ number_format($usdTotal, 2) }}</td></tr>
                @if($exchangeRate > 0)
                    <tr><td colspan="3" class="text-end">សរុបជារៀល</td><td class="text-end">៛ {{ number_format($usdTotal * $exchangeRate, 2) }}</td></tr>
                @endif
                @if($settlementSummary && $settlementSummary['outstanding'] > 0)
                    <tr><td colspan="3" class="text-end">បានបង់</td><td class="text-end">{{ $currency }} {{ number_format($settlementSummary['paid'], 2) }}</td></tr>
                    <tr><td colspan="3" class="text-end">ជំពាក់</td><td class="text-end text-danger">{{ $currency }} {{ number_format($settlementSummary['outstanding'], 2) }}</td></tr>
                @endif
                @if($settlementSummary && $settlementSummary['refunded'] > 0)
                    <tr><td colspan="3" class="text-end">បានសង</td><td class="text-end text-danger">{{ $currency }} {{ number_format($settlementSummary['refunded'], 2) }}</td></tr>
                    <tr><td colspan="3" class="text-end">នៅសល់</td><td class="text-end">{{ $currency }} {{ number_format($settlementSummary['net_paid'], 2) }}</td></tr>
                @endif
            </tfoot>
        </table>
    </section>

    <section class="invoice-section">
        <h2>ព័ត៌មានការទូទាត់</h2>
        <div class="invoice-info-grid payment-info-grid">
            <div><strong>វិធីបង់ប្រាក់:</strong> {{ match($payment->payment_method) { 'cash' => 'សាច់ប្រាក់', 'bank' => 'ABA / ACLEDA', 'qr' => 'WING / QR', default => 'ផ្សេងៗ' } }}</div>
            <div><strong>រូបិយប័ណ្ណ:</strong> {{ $currency }}</div>
            <div><strong>គិតចាប់ពីថ្ងៃទី:</strong> {{ $originalStartDate?->format('d/m/Y') ?? '—' }}</div>
            <div><strong>បង់ថ្មីនៅថ្ងៃទី:</strong> {{ $payment->next_payment_date?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </section>

    <div class="invoice-signatures">
        <div><span>អ្នកទទួលប្រាក់</span><small>ហត្ថលេខា</small><i></i><strong>{{ $receiverName }}</strong></div>
        <div><span>អ្នកត្រួតពិនិត្យ</span><small>ហត្ថលេខា</small><i></i><strong>&nbsp;</strong></div>
        <div><span>អ្នកបង់ប្រាក់</span><small>ហត្ថលេខា</small><i></i><strong>&nbsp;</strong></div>
    </div>

    <footer class="invoice-footer">
        
        <div></div>
        <span>{{ $tagline }}</span>
    </footer>
</section>