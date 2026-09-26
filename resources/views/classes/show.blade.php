@extends('layouts.app')

@section('title', 'ព័ត៌មានថ្នាក់')
@section('page-title', 'ព័ត៌មានថ្នាក់')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>{{ $schoolClass->name }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><strong>ឆ្នាំសិក្សា:</strong> {{ $schoolClass->academicYear?->name ?? '—' }}</div>
            <div class="col-md-6"><strong>ថ្នាក់:</strong> {{ $schoolClass->grade?->name ?? '—' }}</div>
            <div class="col-md-6"><strong>គ្រូបង្រៀន:</strong> {{ $schoolClass->teacher?->full_name_kh ?: $schoolClass->teacher?->full_name ?: '—' }}</div>
            <div class="col-md-6"><strong>បន្ទប់:</strong> {{ $schoolClass->room ?: '—' }}</div>
            <div class="col-md-6"><strong>សមត្ថកិច្ច:</strong> {{ $schoolClass->capacity }}</div>
            <div class="col-md-6"><strong>សាខា:</strong> {{ $schoolClass->branch?->name_kh ?: $schoolClass->branch?->name ?: '—' }}</div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a>
            <a href="{{ route('classes.edit', $schoolClass) }}" class="btn btn-primary">កែប្រែ</a>
        </div>
    </div>
</div>
@endsection
