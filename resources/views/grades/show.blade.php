@extends('layouts.app')

@section('title', 'ព័ត៌មានថ្នាក់')
@section('page-title', 'ព័ត៌មានថ្នាក់')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>{{ $grade->name }}</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6"><strong>កម្រិត:</strong> {{ $grade->level }}</div>
            <div class="col-md-6"><strong>សាខា:</strong> {{ $grade->branch?->name_kh ?: $grade->branch?->name ?: '—' }}</div>
            <div class="col-12"><strong>ពណ៌នា:</strong> {{ $grade->description ?: '—' }}</div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a>
            <a href="{{ route('grades.edit', $grade) }}" class="btn btn-primary">កែប្រែ</a>
        </div>
    </div>
</div>
@endsection
