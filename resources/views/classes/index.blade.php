@extends('layouts.app')

@section('title', 'ថ្នាក់រៀន')
@section('page-title', 'ថ្នាក់រៀន')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-chalkboard text-primary me-2"></i>ថ្នាក់រៀន</h2>
        <p class="page-header-subtitle">{{ $classes->total() }} ថ្នាក់</p>
    </div>
    <a href="{{ route('classes.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតថ្នាក់</a>
</div>

<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="ស្វែងរកថ្នាក់..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="academic_year_id" class="form-select">
                <option value="">-- ឆ្នាំសិក្សាទាំងអស់ --</option>
                @foreach($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" {{ request('academic_year_id') == $academicYear->id ? 'selected' : '' }}>{{ $academicYear->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>តម្រង</button>
            <a href="{{ route('classes.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i>លុប</a>
        </div>
    </form>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ឈ្មោះថ្នាក់</th>
                    <th>ឆ្នាំ</th>
                    <th>ថ្នាក់</th>
                    <th>គ្រូ</th>
                    <th>បន្ទប់</th>
                    <th>វេនសិក្សា</th>
                    <th>សាខា</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($classes->currentPage() - 1) * $classes->perPage() }}</td>
                    <td class="fw-medium">{{ $class->name }}</td>
                    <td>{{ $class->academicYear?->name }}</td>
                    <td>{{ $class->grade?->name }}</td>
                    <td>{{ $class->teacher?->full_name_kh ?: $class->teacher?->full_name ?: '—' }}</td>
                    <td>{{ $class->room ?: '—' }}</td>
                    <td>{{ $class->shift?->name ?: '—' }}</td>
                    <td>{{ $class->branch?->name_kh ?: $class->branch?->name ?: '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('classes.show', $class) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('classes.edit', $class) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('classes.destroy', $class) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបថ្នាក់នេះមែនទេ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9"><div class="empty-state"><i class="fas fa-chalkboard"></i><h5>មិនមានថ្នាក់</h5><p>ចុចប៊ូតុងខាងលើដើម្បីបង្កើត</p></div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($classes->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">បង្ហាញ {{ $classes->firstItem() }}–{{ $classes->lastItem() }} ក្នុង {{ $classes->total() }} ថ្នាក់</div>
            {{ $classes->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
