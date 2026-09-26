@extends('layouts.app')

@section('title', 'កែប្រែឆ្នាំសិក្សា')
@section('page-title', 'កែប្រែឆ្នាំសិក្សា')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar-edit me-2"></i>កែប្រែឆ្នាំសិក្សា</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('academic-years.update', $academicYear) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ឈ្មោះឆ្នាំសិក្សា</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $academicYear->name) }}" required>
                </div>
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-6">
                    <label class="form-label">សាខា</label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $academicYear->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">ថ្ងៃចាប់ផ្តើម</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $academicYear->start_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ថ្ងៃបញ្ចប់</label>
                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $academicYear->end_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">សភាព</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $academicYear->is_active ? 1 : 0) == 1 ? 'selected' : '' }}>សកម្ម</option>
                        <option value="0" {{ old('is_active', $academicYear->is_active ? 1 : 0) == 0 ? 'selected' : '' }}>អសកម្ម</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('academic-years.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
                <button type="submit" class="btn btn-primary">ធ្វើបច្ចុប្បន្នភាព</button>
            </div>
        </form>
    </div>
</div>
@endsection
