@extends('layouts.app')

@section('title', 'ថ្នាក់រៀន')
@section('page-title', 'ថ្នាក់រៀន')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-layer-group text-primary me-2"></i>ថ្នាក់រៀន</h2>
        <p class="page-header-subtitle">{{ $grades->total() }} ថ្នាក់</p>
    </div>
    <a href="{{ route('grades.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតថ្នាក់</a>
</div>

<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="ស្វែងរកថ្នាក់..." value="{{ request('search') }}">
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>តម្រង</button>
            <a href="{{ route('grades.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i>លុប</a>
        </div>
    </form>
</div>

<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ឈ្មោះ</th>
                    <th>កម្រិត</th>
                    <th>សាខា</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grades as $grade)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($grades->currentPage() - 1) * $grades->perPage() }}</td>
                    <td class="fw-medium">{{ $grade->name }}</td>
                    <td>{{ $grade->level }}</td>
                    <td>{{ $grade->branch?->name_kh ?: $grade->branch?->name ?: '—' }}</td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('grades.show', $grade) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('grades.edit', $grade) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('grades.destroy', $grade) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបថ្នាក់នេះមែនទេ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5"><div class="empty-state"><i class="fas fa-layer-group"></i><h5>មិនមានថ្នាក់</h5><p>ចុចប៊ូតុងខាងលើដើម្បីបង្កើត</p></div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($grades->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">បង្ហាញ {{ $grades->firstItem() }}–{{ $grades->lastItem() }} ក្នុង {{ $grades->total() }} ថ្នាក់</div>
            {{ $grades->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
