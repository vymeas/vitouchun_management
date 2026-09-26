@extends('layouts.app')

@section('title', 'ព័ត៌មានឆ្នាំសិក្សា')
@section('page-title', 'ព័ត៌មានឆ្នាំសិក្សា')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>{{ $academicYear->name }}</h5>
        <div>
            @if($academicYear->is_active)
                <span class="badge badge-active">សកម្ម</span>
            @else
                <span class="badge badge-inactive">អសកម្ម</span>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><strong>សាខា:</strong> {{ $academicYear->branch?->name_kh ?: $academicYear->branch?->name ?: '—' }}</div>
            <div class="col-md-6"><strong>ថ្ងៃចាប់ផ្តើម:</strong> {{ $academicYear->start_date?->format('d/m/Y') }}</div>
            <div class="col-md-6"><strong>ថ្ងៃបញ្ចប់:</strong> {{ $academicYear->end_date?->format('d/m/Y') }}</div>
            <div class="col-md-6"><strong>បានបង្កើត:</strong> {{ $academicYear->created_at?->format('d/m/Y H:i') }}</div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('academic-years.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a>
            <a href="{{ route('academic-years.edit', $academicYear) }}" class="btn btn-primary">កែប្រែ</a>
        </div>
    </div>
</div>
@endsection
