@extends('layouts.app')

@section('title', 'អ្នកប្រើប្រាស់')
@section('page-title', 'អ្នកប្រើប្រាស់')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-users-cog text-primary me-2"></i>អ្នកប្រើប្រាស់ប្រព័ន្ធ
        </h2>
        <p class="page-header-subtitle">{{ $users->total() }} អ្នកប្រើប្រាស់</p>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i>បង្កើតអ្នកប្រើប្រាស់ថ្មី
    </a>
</div>

{{-- Filter Bar --}}
<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label small text-muted">ស្វែងរក</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="ឈ្មោះ, username, អ៊ីម៉ែល..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <label class="form-label small text-muted">តួនាទី</label>
            <select name="role" class="form-select">
                <option value="">-- ទាំងអស់ --</option>
                @foreach($roles as $key => $label)
                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        
        @if(auth()->user()->isSuperAdmin())
        <div class="col-md-2">
            <label class="form-label small text-muted">សាខា</label>
            <select name="branch_id" class="form-select">
                <option value="">-- ទាំងអស់ --</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name_kh ?: $branch->name }}
                </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="col-md-2">
            <label class="form-label small text-muted">សភាព</label>
            <select name="status" class="form-select">
                <option value="">-- ទាំងអស់ --</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>សកម្ម</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>អសកម្ម</option>
                <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>ផ្អាក</option>
            </select>
        </div>

        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i>តម្រង
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>លុប
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card table-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>អ្នកប្រើប្រាស់</th>
                    <th>Username</th>
                    <th>តួនាទី</th>
                    <th>សាខា</th>
                    <th>សភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                {{ strtoupper(substr($user->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-medium">{{ $user->full_name_kh ?: $user->full_name }}</div>
                                <div class="text-muted small">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fw-normal font-monospace">{{ $user->username }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $user->role_label }}</span>
                    </td>
                    <td>
                        @if($user->branch)
                            <span class="small">{{ $user->branch->name_kh ?: $user->branch->name }}</span>
                        @else
                            <span class="badge bg-light text-dark">គ្រប់សាខា</span>
                        @endif
                    </td>
                    <td>
                        @if($user->status == 'active')
                            <span class="badge badge-active">សកម្ម</span>
                        @elseif($user->status == 'inactive')
                            <span class="badge badge-inactive">អសកម្ម</span>
                        @else
                            <span class="badge badge-suspended">ផ្អាក</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="មើល">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="កែប្រែ">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('users.permissions', $user) }}" class="btn btn-sm btn-outline-warning" title="សិទ្ធិ">
                                <i class="fas fa-key"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបគណនីនេះមែនទេ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="លុប">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h5>មិនមានអ្នកប្រើប្រាស់</h5>
                            <p>ចុចប៊ូតុងខាងលើដើម្បីបង្កើតអ្នកប្រើប្រាស់ថ្មី</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                បង្ហាញ {{ $users->firstItem() }}–{{ $users->lastItem() }} ក្នុង {{ $users->total() }} អ្នកប្រើប្រាស់
            </div>
            {{ $users->links() }}
        </div>
    </div>
    @endif
</div>

@endsection
