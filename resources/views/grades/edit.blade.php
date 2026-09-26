@extends('layouts.app')

@section('title', 'កែប្រែថ្នាក់')
@section('page-title', 'កែប្រែថ្នាក់')

@section('content')
<div class="card mx-auto" style="max-width: 760px;">
    <div class="card-header"><h5 class="mb-0"><i class="fas fa-edit me-2"></i>កែប្រែថ្នាក់</h5></div>
    <div class="card-body">
        <form method="POST" action="{{ route('grades.update', $grade) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">ឈ្មោះថ្នាក់</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $grade->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">កម្រិត</label>
                    <input type="number" name="level" class="form-control" value="{{ old('level', $grade->level) }}" min="1" max="12" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">កម្រិតសិក្សា</label>
                    <select name="education_level" class="form-select">
                        <option value="">-- មិនកំណត់ --</option>
                        <option value="preschool" {{ old('education_level', $grade->education_level) === 'preschool' ? 'selected' : '' }}>មត្តេយ្យ</option>
                        <option value="primary" {{ old('education_level', $grade->education_level) === 'primary' ? 'selected' : '' }}>បឋមសិក្សា</option>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">តម្លៃសិក្សាប្រចាំខែ (USD)</label><input type="number" step="0.01" name="monthly_tuition_fee" class="form-control" value="{{ old('monthly_tuition_fee', $grade->monthly_tuition_fee) }}" min="0" required></div>
                @if(auth()->user()->isSuperAdmin())
                <div class="col-md-12">
                    <label class="form-label">សាខា</label>
                    <select name="branch_id" class="form-select" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $grade->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-12">
                    <label class="form-label">ពណ៌នា</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $grade->description) }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
                <button type="submit" class="btn btn-primary">ធ្វើបច្ចុប្បន្នភាព</button>
            </div>
        </form>
    </div>
</div>
@endsection
