@extends('layouts.app')

@section('title', 'ព័ត៌មានសាខា')
@section('page-title', 'ព័ត៌មានសាខា')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-building text-primary me-2"></i>{{ $branch->name_kh ?: $branch->name }}
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">គ្រប់គ្រងសាខា</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $branch->code }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('branches.edit', $branch) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i>កែប្រែ
        </a>
        <form action="{{ route('branches.destroy', $branch) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបសាខានេះមែនទេ?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i>លុប
            </button>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-info-circle text-primary me-2"></i>ព័ត៌មានទូទៅ
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <div>
                        <div class="text-muted small">លេខកូដសាខា (Code)</div>
                        <div class="fw-medium font-monospace">{{ $branch->code }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">ឈ្មោះសាខា (ខ្មែរ)</div>
                        <div class="fw-medium">{{ $branch->name_kh ?: '—' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">ឈ្មោះសាខា (English)</div>
                        <div class="fw-medium">{{ $branch->name }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">សភាព (Status)</div>
                        <div class="mt-1">
                            @if($branch->status == 'active')
                                <span class="badge badge-active">សកម្ម</span>
                            @elseif($branch->status == 'inactive')
                                <span class="badge badge-inactive">អសកម្ម</span>
                            @else
                                <span class="badge bg-danger text-white">បិទ</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-muted small">ថ្ងៃបើកដំណើរការ</div>
                        <div class="fw-medium">{{ $branch->opened_at?->format('d/m/Y') ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-address-book text-success me-2"></i>ព័ត៌មានទំនាក់ទំនង
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="text-muted small"><i class="fas fa-phone me-1"></i>លេខទូរស័ព្ទ</div>
                                <div class="fw-medium mt-1">{{ $branch->phone ?: '—' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <div class="text-muted small"><i class="fas fa-envelope me-1"></i>អ៊ីម៉ែល</div>
                                <div class="fw-medium mt-1">{{ $branch->email ?: '—' }}</div>
                            </div>
                            <div class="col-12">
                                <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>អាសយដ្ឋាន</div>
                                <div class="fw-medium mt-1">{{ $branch->address ?: '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-users text-warning me-2"></i>អ្នកគ្រប់គ្រងប្រចាំសាខា
                        </div>
                        <span class="badge bg-light text-dark border">{{ $branch->users->count() }} នាក់</span>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ឈ្មោះ</th>
                                    <th>តួនាទី</th>
                                    <th>ទូរស័ព្ទ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($branch->users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $user->full_name_kh ?: $user->full_name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $user->role_label }}</span></td>
                                    <td class="text-muted small">{{ $user->phone ?: '—' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="text-center text-muted small py-4">មិនមានអ្នកគ្រប់គ្រងទេ</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
