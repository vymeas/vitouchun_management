@extends('layouts.app')

@section('title', 'ឆ្នាំសិក្សា')
@section('page-title', 'ឆ្នាំសិក្សា')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-calendar-alt text-primary me-2"></i>ឆ្នាំសិក្សា</h2>
        <p class="page-header-subtitle">{{ $academicYears->total() }} ជំនាន់</p>
    </div>
    <a href="{{ route('academic-years.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតឆ្នាំសិក្សា</a>
</div>

<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="ស្វែងរកឆ្នាំសិក្សា..." value="{{ request('search') }}">
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-md-3">
            <select name="branch_id" class="form-select">
                <option value="">-- ទាំងអស់ --</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>តម្រង</button>
            <a href="{{ route('academic-years.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i>លុប</a>
        </div>
    </form>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ឆ្នាំសិក្សា</th>
                    <th>សាខា</th>
                    <th>ចាប់ផ្តើម</th>
                    <th>បញ្ចប់</th>
                    <th>សភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($academicYears as $academicYear)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($academicYears->currentPage() - 1) * $academicYears->perPage() }}</td>
                    <td class="fw-medium">{{ $academicYear->name }}</td>
                    <td>{{ $academicYear->branch?->name_kh ?: $academicYear->branch?->name ?: '—' }}</td>
                    <td>{{ $academicYear->start_date?->format('d/m/Y') }}</td>
                    <td>{{ $academicYear->end_date?->format('d/m/Y') }}</td>
                    <td>
                        @if($academicYear->is_active)
                            <span class="badge badge-active">សកម្ម</span>
                        @else
                            <span class="badge badge-inactive">អសកម្ម</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('academic-years.show', $academicYear) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('academic-years.edit', $academicYear) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('academic-years.destroy', $academicYear) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបឆ្នាំសិក្សានេះមែនទេ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state"><i class="fas fa-calendar-alt"></i><h5>មិនមានឆ្នាំសិក្សា</h5><p>ចុចប៊ូតុងខាងលើដើម្បីបង្កើតថ្មី</p></div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($academicYears->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">បង្ហាញ {{ $academicYears->firstItem() }}–{{ $academicYears->lastItem() }} ក្នុង {{ $academicYears->total() }} ជំនាន់</div>
            {{ $academicYears->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
