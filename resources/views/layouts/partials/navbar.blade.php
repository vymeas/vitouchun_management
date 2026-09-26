{{-- Top Navigation Bar --}}
<header class="app-navbar">
    <div class="navbar-left">
        {{-- Sidebar Toggle --}}
        <button class="sidebar-toggler btn" id="sidebarToggler" title="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        {{-- Page Title --}}
        <h1 class="page-title">@yield('page-title', 'ផ្ទាំងគ្រប់គ្រង')</h1>
    </div>

    <div class="navbar-right d-flex align-items-center gap-3">

        {{-- Branch Switcher (Super Admin only) --}}
        @if(auth()->user()->isSuperAdmin())
        <div class="branch-switcher d-none d-md-block">
            <select class="form-select form-select-sm" id="branchSwitcher" onchange="switchBranch(this.value)">
                <option value="">🌐 គ្រប់សាខា</option>
                @php $branches = \App\Models\Branch::active()->orderBy('name')->get(); @endphp
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ session('active_branch_id') == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name_kh ?: $branch->name }}
                </option>
                @endforeach
            </select>
        </div>
        @else
        <div class="branch-badge d-none d-sm-flex align-items-center gap-1">
            <i class="fas fa-code-branch text-primary small"></i>
            <span class="small text-muted">{{ auth()->user()->branch?->name_kh ?: auth()->user()->branch?->name }}</span>
        </div>
        @endif

        {{-- Notifications (placeholder) --}}
        <div class="dropdown">
            <button class="btn btn-icon position-relative" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <span>ការជូនដំណឹង</span>
                </div>
                <div class="dropdown-divider"></div>
                <div class="text-center py-3 text-muted small">
                    <i class="fas fa-bell-slash mb-2 d-block fs-4"></i>
                    មិនមានការជូនដំណឹង
                </div>
            </div>
        </div>

        {{-- User Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-icon d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                <div class="user-avatar-sm">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                </div>
                <span class="d-none d-md-block small fw-medium">{{ auth()->user()->display_name }}</span>
                <i class="fas fa-chevron-down small text-muted d-none d-md-block"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li>
                    <div class="dropdown-item-text py-2">
                        <div class="fw-medium">{{ auth()->user()->display_name }}</div>
                        <div class="text-muted small">{{ auth()->user()->role_label }}</div>
                        @if(auth()->user()->email)
                        <div class="text-muted small">{{ auth()->user()->email }}</div>
                        @endif
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-user-edit me-2 text-muted"></i>គណនីរបស់ខ្ញុំ
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('settings.index') }}">
                        <i class="fas fa-cog me-2 text-muted"></i>ការកំណត់
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>ចាកចេញ
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

@push('scripts')
<script>
function switchBranch(branchId) {
    fetch('/api/switch-branch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ branch_id: branchId })
    }).then(() => window.location.reload());
}
</script>
@endpush
