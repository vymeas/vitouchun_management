@extends('layouts.app')

@section('title', 'បង្កើតថ្នាក់')
@section('page-title', 'បង្កើតថ្នាក់')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-chalkboard me-2"></i>បង្កើតថ្នាក់</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('classes.store') }}">
            @csrf
            <div class="row g-3">
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-6">
                    <label class="form-label">សាខា</label>
                    <select name="branch_id" class="form-select" required>
                        <option value="">-- ជ្រើសរើសសាខា --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">ឆ្នាំសិក្សា</label>
                    <select name="academic_year_id" class="form-select" required>
                        <option value="">-- ជ្រើសរើសឆ្នាំ --</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" {{ old('academic_year_id') == $academicYear->id ? 'selected' : '' }}>{{ $academicYear->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ថ្នាក់</label>
                    <select name="grade_id" class="form-select" required>
                        <option value="">-- ជ្រើសរើសថ្នាក់ --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ឈ្មោះថ្នាក់</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">គ្រូបង្រៀន</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">-- គ្មាន --</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->full_name_kh ?: $teacher->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">បន្ទប់</label>
                    <input type="text" name="room" class="form-control" value="{{ old('room') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">វេនសិក្សា</label>
                    <select name="shift_id" class="form-select">
                        <option value="">-- មិនកំណត់ --</option>
                        @foreach($shifts as $shift)
                            <option value="{{ $shift->id }}" @selected(old('shift_id') == $shift->id)>{{ $shift->name }} ({{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">សមត្ថកិច្ច</label>
                    <input type="number" name="capacity" class="form-control" value="{{ old('capacity', 40) }}" min="1" max="200" required>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
                <button type="submit" class="btn btn-primary">រក្សាទុក</button>
            </div>
        </form>
    </div>
</div>
@endsection
