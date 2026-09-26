@extends('layouts.app')

@section('title', 'គ្រប់គ្រងសាខា')
@section('page-title', 'គ្រប់គ្រងសាខា')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-code-branch text-primary me-2"></i>គ្រប់គ្រងសាខា
        </h2>
        <p class="page-header-subtitle">{{ $branches->total() }} សាខា</p>
    </div>
    <a href="{{ route('branches.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>បន្ថែមសាខា
    </a>
</div>

{{-- Filter Bar --}}
<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="ស្វែងរកសាខា..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- សភាពទាំងអស់ --</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>សកម្ម</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>អសកម្ម</option>
                <option value="closed"   {{ request('status') == 'closed'   ? 'selected' : '' }}>បិទ</option>
            </select>
        </div>
        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i>តម្រង
            </button>
            <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>លុប
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>លេខកូដ</th>
                    <th>ឈ្មោះសាខា</th>
                    <th>ទូរស័ព្ទ</th>
                    <th>អ៊ីម៉ែល</th>
                    <th>សភាព</th>
                    <th>បើក</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($branches->currentPage() - 1) * $branches->perPage() }}</td>
                    <td>
                        <span class="badge bg-light text-dark border fw-normal font-monospace">{{ $branch->code }}</span>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $branch->name_kh ?: $branch->name }}</div>
                        @if($branch->name_kh && $branch->name)
                        <div class="text-muted small">{{ $branch->name }}</div>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $branch->phone ?: '—' }}</td>
                    <td class="text-muted small">{{ $branch->email ?: '—' }}</td>
                    <td>
                        @if($branch->status == 'active')
                            <span class="badge badge-active">សកម្ម</span>
                        @elseif($branch->status == 'inactive')
                            <span class="badge badge-inactive">អសកម្ម</span>
                        @else
                            <span class="badge bg-danger text-white">បិទ</span>
                        @endif
                    </td>
                    <td class="small text-muted">
                        {{ $branch->opened_at?->format('d/m/Y') ?: '—' }}
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('branches.show', $branch) }}" class="btn btn-sm btn-outline-secondary" title="មើល">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('branches.edit', $branch) }}" class="btn btn-sm btn-outline-primary" title="កែ">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបសាខានេះ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="លុប">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-code-branch"></i>
                            <h5>មិនមានសាខា</h5>
                            <p>ចុចប៊ូតុង "បន្ថែមសាខា" ដើម្បីបន្ថែម</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($branches->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                បង្ហាញ {{ $branches->firstItem() }}–{{ $branches->lastItem() }} ក្នុង {{ $branches->total() }} សាខា
            </div>
            {{ $branches->links() }}
        </div>
    </div>
    @endif
</div>

@endsection
