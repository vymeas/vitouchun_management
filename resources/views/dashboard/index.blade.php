@extends('layouts.app')

@section('title', 'ផ្ទាំងគ្រប់គ្រង')
@section('page-title', 'ផ្ទាំងគ្រប់គ្រង')

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-tachometer-alt text-primary me-2"></i>ផ្ទាំងគ្រប់គ្រង
        </h2>
        <p class="page-header-subtitle">
            {{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }}
            @if(auth()->user()->branch)
                · {{ auth()->user()->branch->name_kh ?: auth()->user()->branch->name }}
            @endif
        </p>
    </div>
    <div class="text-end">
        <div class="text-muted small">
            <i class="fas fa-clock me-1"></i>
            <span id="currentTime"></span>
        </div>
        <div class="text-muted small">
            <i class="fas fa-calendar me-1"></i>
            {{ now()->locale('km')->isoFormat('dddd, D MMMM YYYY') }}
        </div>
    </div>
</div>

{{-- ========= STAT CARDS ========= --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['total_students']) }}</div>
                <div class="stat-label">សិស្សទាំងអស់</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['active_students']) }}</div>
                <div class="stat-label">សិស្សកំពុងសិក្សា</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['total_classes']) }}</div>
                <div class="stat-label">ថ្នាក់រៀន</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['total_staff']) }}</div>
                <div class="stat-label">បុគ្គលិក</div>
            </div>
        </div>
    </div>

</div>

{{-- ========= FINANCIAL STAT CARDS ========= --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-hand-holding-dollar"></i>
            </div>
            <div>
                <div class="stat-value">${{ number_format($stats['today_payment'], 2) }}</div>
                <div class="stat-label">ប្រាក់ទទួលថ្ងៃនេះ</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-chart-line"></i>
            </div>
            <div>
                <div class="stat-value">${{ number_format($stats['monthly_revenue'], 2) }}</div>
                <div class="stat-label">ចំណូលខែនេះ</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4">
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <div class="stat-value">${{ number_format($stats['outstanding'], 2) }}</div>
                <div class="stat-label">ប្រាក់ជំពាក់</div>
            </div>
        </div>
    </div>

</div>

{{-- ========= CHARTS ROW ========= --}}
<div class="row g-3 mb-4">

    {{-- Payment Chart --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-chart-bar text-primary me-2"></i>ប្រវត្តិការទទួលប្រាក់ (12 ខែ)</span>
                <div class="d-flex gap-2">
                    <span class="badge" style="background:#dbeafe;color:#1d4ed8;">USD</span>
                </div>
            </div>
            <div class="card-body">
                <canvas id="paymentChart" height="300"></canvas>
                <div class="empty-state py-4" id="paymentChartEmpty">
                    <i class="fas fa-chart-bar"></i>
                    <h5>មិនទាន់មានទិន្នន័យ</h5>
                    <p>ទិន្នន័យការទទួលប្រាក់នឹងបង្ហាញនៅទីនេះ</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Enrollment Donut --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-chart-pie text-purple me-2"></i>ស្ថិតិសិស្ស
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <canvas id="studentChart" width="200" height="200" style="max-width:200px"></canvas>
                <div class="mt-3 w-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:12px;height:12px;background:#2563eb;border-radius:3px;display:inline-block"></span>
                            <span class="small">កំពុងសិក្សា</span>
                        </div>
                        <span class="fw-bold small">{{ $stats['active_students'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:12px;height:12px;background:#e2e8f0;border-radius:3px;display:inline-block"></span>
                            <span class="small">ផ្សេងៗ</span>
                        </div>
                        <span class="fw-bold small">{{ $stats['total_students'] - $stats['active_students'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ========= QUICK ACTIONS ========= --}}
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt text-warning me-2"></i>សកម្មភាពរហ័ស
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="#" class="btn btn-outline-primary w-100 py-3 flex-column gap-1 justify-content-center">
                            <i class="fas fa-user-plus fs-5"></i>
                            <span>ចុះឈ្មោះសិស្ស</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#" class="btn btn-outline-success w-100 py-3 flex-column gap-1 justify-content-center">
                            <i class="fas fa-hand-holding-dollar fs-5"></i>
                            <span>ទទួលប្រាក់</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#" class="btn btn-outline-warning w-100 py-3 flex-column gap-1 justify-content-center">
                            <i class="fas fa-clipboard-check fs-5"></i>
                            <span>វត្តមានសិស្ស</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="#" class="btn btn-outline-info w-100 py-3 flex-column gap-1 justify-content-center">
                            <i class="fas fa-chart-bar fs-5"></i>
                            <span>របាយការណ៍</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Digital Clock
function updateTime() {
    const el = document.getElementById('currentTime');
    if (el) el.textContent = new Date().toLocaleTimeString('km-KH');
}
updateTime();
setInterval(updateTime, 1000);

// Payment Chart
const paymentCtx = document.getElementById('paymentChart');
if (paymentCtx) {
    const months = ['មករា','កុម្ភៈ','មីនា','មេសា','ឧសភា','មិថុនា','កក្កដា','សីហា','កញ្ញា','តុលា','វិច្ឆិកា','ធ្នូ'];
    document.getElementById('paymentChartEmpty')?.style.setProperty('display', 'none', 'important');
    new Chart(paymentCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'ការទទួលប្រាក់ (USD)',
                data: [0,0,0,0,0,0,0,0,0,0,0,0],
                backgroundColor: 'rgba(37, 99, 235, 0.15)',
                borderColor: '#2563eb',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => '$' + ctx.parsed.y.toFixed(2)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => '$' + v },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false } }
            }
        }
    });
    document.getElementById('paymentChartEmpty').style.display = 'none';
}

// Student Donut
const studentCtx = document.getElementById('studentChart');
if (studentCtx) {
    new Chart(studentCtx, {
        type: 'doughnut',
        data: {
            labels: ['កំពុងសិក្សា', 'ផ្សេងៗ'],
            datasets: [{
                data: [{{ $stats['active_students'] }}, {{ max(0, $stats['total_students'] - $stats['active_students']) }}],
                backgroundColor: ['#2563eb', '#e2e8f0'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '70%',
            plugins: { legend: { display: false } },
            responsive: true,
        }
    });
}
</script>
@endpush
