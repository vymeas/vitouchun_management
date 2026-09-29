{{-- Top Navigation Bar --}}
<style>
    /* =========================================
   TOP NAVBAR - SINGLE ROW
========================================= */

.app-navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: nowrap !important;
    width: 100%;
    min-width: 0;
    gap: 12px;
}

.navbar-left,
.navbar-right {
    display: flex;
    align-items: center;
    flex-wrap: nowrap !important;
    min-width: 0;
}

.navbar-left {
    flex: 1 1 auto;
    overflow: hidden;
}

.navbar-right {
    flex: 0 0 auto;
    white-space: nowrap;
}

/* Page title */
.app-navbar .page-title {
    white-space: nowrap !important;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Branch */
.app-navbar .branch-switcher,
.app-navbar .branch-badge {
    white-space: nowrap !important;
    flex-shrink: 0;
}

.app-navbar .branch-switcher .form-select {
    white-space: nowrap;
    min-width: 150px;
}

/* User */
.app-navbar .user-avatar-sm {
    flex-shrink: 0;
}

.app-navbar .dropdown-toggle,
.app-navbar button {
    white-space: nowrap !important;
}

/* Dropdown */
.app-navbar .dropdown-menu {
    white-space: nowrap;
}

/* Prevent Khmer text from breaking */
.app-navbar span,
.app-navbar h1,
.app-navbar a,
.app-navbar button,
.app-navbar select {
    word-break: keep-all;
    overflow-wrap: normal;
}

/* Desktop */
@media (min-width: 769px) {
    .app-navbar {
        min-height: 64px;
    }
}

/* Smaller screens */
@media (max-width: 768px) {
    .app-navbar {
        gap: 6px;
    }

    .navbar-right {
        gap: 4px !important;
    }

    .app-navbar .branch-switcher .form-select {
        min-width: 125px;
        max-width: 150px;
    }

    .app-navbar .page-title {
        font-size: 1rem;
    }
}
/* =========================================
   USER MENU
========================================= */

.user-menu-btn {
    border: 0 !important;
    background: transparent !important;
    padding: 4px 8px !important;
    margin: 0;
    width: auto !important;
    height: auto !important;
    min-width: max-content;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    white-space: nowrap !important;
    box-shadow: none !important;
}

.user-menu-btn:hover,
.user-menu-btn:focus,
.user-menu-btn:active {
    background: rgba(0, 0, 0, 0.04) !important;
    box-shadow: none !important;
}

.user-name {
    display: inline-block !important;
    width: auto !important;
    max-width: none !important;
    white-space: nowrap !important;
    overflow: visible !important;
    text-overflow: clip !important;
    font-size: 14px;
    font-weight: 500;
}

.user-menu-arrow {
    font-size: 10px;
    color: #010101;
    flex-shrink: 0;
}

/* Avatar */
.user-avatar-sm {
    width: 34px;
    height: 34px;
    min-width: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-weight: 600;
}

/* Prevent the whole user area from becoming a box */
.app-navbar .user-menu-btn {
    width: auto !important;
    min-width: max-content !important;
    max-width: none !important;
}

/* Khmer text */
.app-navbar .user-name {
    word-break: keep-all !important;
    overflow-wrap: normal !important;
}
</style>

<header class="app-navbar">

    <div class="navbar-left d-flex align-items-center flex-nowrap">
        {{-- Sidebar Toggle --}}
        <button
            class="sidebar-toggler btn flex-shrink-0"
            id="sidebarToggler"
            title="Toggle Sidebar"
            type="button"
        >
            <i class="fas fa-bars"></i>
        </button>

        {{-- Page Title --}}
        <h1 class="page-title text-nowrap mb-0">
            @yield('page-title', 'ផ្ទាំងគ្រប់គ្រង')
        </h1>
    </div>

    <div class="navbar-right d-flex align-items-center flex-nowrap gap-2">

        {{-- Branch Switcher --}}
        @if(auth()->user()->isSuperAdmin())

            <div class="branch-switcher flex-shrink-0">
                <select
                    class="form-select form-select-sm text-nowrap"
                    id="branchSwitcher"
                    onchange="switchBranch(this.value)"
                >
                    <option value="">🌐 គ្រប់សាខា</option>

                    @php
                        $branches = \App\Models\Branch::active()
                            ->orderBy('name')
                            ->get();
                    @endphp

                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            {{ session('active_branch_id') == $branch->id ? 'selected' : '' }}
                        >
                            {{ $branch->name_kh ?: $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        @else

            <div class="branch-badge d-flex align-items-center gap-1 flex-shrink-0 text-nowrap">
                <i class="fas fa-code-branch text-primary small"></i>

                <span class="small text-muted text-nowrap">
                    {{ auth()->user()->branch?->name_kh ?: auth()->user()->branch?->name }}
                </span>
            </div>

        @endif

        {{-- Notifications --}}
        <div class="dropdown flex-shrink-0">

            <button
                class="btn btn-icon position-relative"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="ការជូនដំណឹង"
            >
                <i class="fas fa-bell"></i>

                <span class="notification-badge">
                    0
                </span>
            </button>

            <div class="dropdown-menu dropdown-menu-end notification-dropdown">

                <div class="dropdown-header d-flex justify-content-between align-items-center text-nowrap">
                    <span>ការជូនដំណឹង</span>
                </div>

                <div class="dropdown-divider"></div>

                <div class="text-center py-3 text-muted small text-nowrap">
                    <i class="fas fa-bell-slash mb-2 d-block fs-4"></i>
                    មិនមានការជូនដំណឹង
                </div>

            </div>
        </div>

        {{-- User Dropdown --}}
<div class="dropdown flex-shrink-0">

    <button
        type="button"
        class="user-menu-btn d-flex align-items-center gap-2"
        data-bs-toggle="dropdown"
        aria-expanded="false"
    >

        {{-- Avatar --}}
        <div class="user-avatar-sm flex-shrink-0">
            {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
        </div>

        {{-- User Name --}}
        <span class="user-name text-nowrap text-dark">
            {{ auth()->user()->display_name }}
        </span>

        {{-- Arrow --}}
        <i class="fas fa-chevron-down user-menu-arrow"></i>

    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow">

        <li>
            <div class="dropdown-item-text py-2">

                <div class="fw-medium">
                    {{ auth()->user()->display_name }}
                </div>

                <div class="text-muted small">
                    {{ auth()->user()->role_label }}
                </div>

                @if(auth()->user()->email)
                    <div class="text-muted small">
                        {{ auth()->user()->email }}
                    </div>
                @endif

            </div>
        </li>

        <li>
            <hr class="dropdown-divider">
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                <i class="fas fa-user-edit me-2 text-muted"></i>
                គណនីរបស់ខ្ញុំ
            </a>
        </li>

        <li>
            <a class="dropdown-item" href="{{ route('settings.index') }}">
                <i class="fas fa-cog me-2 text-muted"></i>
                ការកំណត់
            </a>
        </li>

        <li>
            <hr class="dropdown-divider">
        </li>

        <li>
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="dropdown-item text-danger"
                >
                    <i class="fas fa-sign-out-alt me-2"></i>
                    ចាកចេញ
                </button>
            </form>
        </li>

    </ul>

</div>
</header>


{{-- Branch Switch Script --}}
@push('scripts')
<script>
function switchBranch(branchId) {
    fetch('/api/switch-branch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector(
                'meta[name="csrf-token"]'
            ).content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            branch_id: branchId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Branch switch failed');
        }

        return response.json().catch(() => ({}));
    })
    .then(() => {
        window.location.reload();
    })
    .catch(error => {
        console.error(error);
    });
}
</script>
@endpush