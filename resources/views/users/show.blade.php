@extends('layouts.app')

@section('title', 'ព័ត៌មានគណនី')
@section('page-title', 'ព័ត៌មានគណនី')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-user text-primary me-2"></i>{{ $user->full_name_kh ?: $user->full_name }}
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">អ្នកប្រើប្រាស់</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $user->username }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i>កែប្រែ
        </a>
        <a href="{{ route('users.permissions', $user) }}" class="btn btn-warning">
            <i class="fas fa-key"></i>សិទ្ធិ (Permissions)
        </a>
        @if($user->id !== auth()->id())
        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបគណនីនេះមែនទេ?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i>លុប
            </button>
        </form>
        @endif
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center pt-4">
                <div class="avatar mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($user->full_name, 0, 1)) }}
                </div>
                <h5 class="mb-1 fw-bold">{{ $user->full_name_kh ?: $user->full_name }}</h5>
                <div class="text-muted small mb-3">{{ $user->email ?: 'គ្មានអ៊ីម៉ែល' }}</div>
                
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <span class="badge bg-light text-dark border">{{ $user->role_label }}</span>
                    @if($user->status == 'active')
                        <span class="badge badge-active">សកម្ម</span>
                    @elseif($user->status == 'inactive')
                        <span class="badge badge-inactive">អសកម្ម</span>
                    @else
                        <span class="badge badge-suspended">ផ្អាក</span>
                    @endif
                </div>

                <ul class="list-group list-group-flush text-start small">
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">Username</span>
                        <span class="fw-medium font-monospace">{{ $user->username }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">ទូរស័ព្ទ</span>
                        <span class="fw-medium">{{ $user->phone ?: '—' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">សាខា</span>
                        <span class="fw-medium">{{ $user->branch ? ($user->branch->name_kh ?: $user->branch->name) : 'គ្រប់សាខា' }}</span>
                    </li>
                    <li class="list-group-item px-0 d-flex justify-content-between">
                        <span class="text-muted">បង្កើតនៅ</span>
                        <span class="fw-medium">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-shield-alt text-success me-2"></i>សិទ្ធិអនុញ្ញាត ({{ $user->permissions->count() }})</span>
            </div>
            <div class="card-body">
                @if($user->isSuperAdmin())
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>គណនីនេះជា <strong>Super Admin</strong> និងមានសិទ្ធិពេញលេញលើប្រព័ន្ធទាំងមូល។
                    </div>
                @elseif($user->permissions->count() == 0)
                    <div class="empty-state py-4">
                        <i class="fas fa-lock text-muted opacity-50"></i>
                        <h5>មិនមានសិទ្ធិបញ្ជាក់</h5>
                        <p class="small text-muted">គណនីនេះមិនទាន់មានសិទ្ធិចូលប្រើផ្នែកណាមួយនៅឡើយទេ។</p>
                        <a href="{{ route('users.permissions', $user) }}" class="btn btn-sm btn-outline-primary mt-2">កំណត់សិទ្ធិឥឡូវនេះ</a>
                    </div>
                @else
                    @php
                        $groupedPerms = $user->permissions->groupBy('module');
                    @endphp
                    
                    <div class="row g-3">
                        @foreach($groupedPerms as $module => $perms)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-uppercase text-secondary fw-bold mb-2">{{ $module }}</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($perms as $perm)
                                    <span class="badge bg-light text-dark border small fw-normal">{{ $perm->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
