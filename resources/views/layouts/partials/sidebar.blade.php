{{-- Sidebar Navigation --}}
<style>
.login-logo {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
    padding: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.login-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
/* =========================
   Sidebar Logo Fix
   ========================= */

.app-sidebar .sidebar-header {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    min-height: 90px !important;
    height: auto !important;
    overflow: visible !important;
}

.app-sidebar .sidebar-brand {
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    min-width: 0 !important;
    overflow: visible !important;
}

.app-sidebar .login-logo {
    width: 70px !important;
    height: 70px !important;
    min-width: 70px !important;
    min-height: 70px !important;
    max-width: 70px !important;
    max-height: 70px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    flex: 0 0 70px !important;
    padding: 8px !important;
    margin: 0 !important;
    border-radius: 50% !important;
    overflow: hidden !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.app-sidebar .login-logo img {
    display: block !important;
    width: 100% !important;
    height: 100% !important;
    min-width: 100% !important;
    min-height: 100% !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: contain !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.app-sidebar .sidebar-brand-text {
    display: flex !important;
    flex-direction: column !important;
    min-width: 0 !important;
    visibility: visible !important;
}

.app-sidebar .brand-name,
.app-sidebar .brand-tagline {
    display: block !important;
}
</style>
<nav id="appSidebar" class="app-sidebar">

            {{-- Logo / School Name --}}
        <div class="sidebar-header">
            <div class="sidebar-brand d-flex align-items-center gap-2">

                <div class="login-logo">
                    <img
                        <img src="{{ asset('storage/images/logo.png') }}"
                        alt="សាលារៀនវិទូជន Logo"
                    >
                </div>

                <div class="sidebar-brand-text">
                    <span class="brand-name">
                        {{ \App\Services\SettingService::get('school_name_kh', 'វិទូជន') }}
                    </span>

                    <span class="brand-tagline">
                        School Management
                    </span>
                </div>

            </div>

            <button class="sidebar-close-btn d-lg-none" id="sidebarClose">
                <i class="fas fa-times"></i>
            </button>
        </div>

    {{-- User Info --}}
    <!-- <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="sidebar-user-info">
            <div class="user-name">{{ auth()->user()->display_name }}</div>
            <div class="user-role">{{ auth()->user()->role_label }}</div>
        </div>
    </div> -->

    {{-- Navigation Menu --}}
    <div class="sidebar-nav">
        <ul class="nav-list">

            {{-- Dashboard --}}
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">ផ្ទាំងគ្រប់គ្រង</span>
                </a>
            </li>

            {{-- ============ ការសិក្សា ============ --}}
            <li class="nav-section-header">
                <span>ការសិក្សា</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('classes.*', 'study-shifts.*', 'students.*', 'subjects.*', 'academic-years.*', 'grades.*', 'enrollments.*') ? 'open' : '' }}">
                <a href="#academicMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-book-open nav-icon"></i>
                    <span class="nav-text">ការសិក្សា</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('classes.*', 'study-shifts.*', 'students.*', 'subjects.*', 'academic-years.*', 'grades.*', 'enrollments.*') ? 'show' : '' }}" id="academicMenu">
                    <li class="{{ request()->routeIs('classes.*') ? 'active' : '' }}">
                        <a href="{{ route('classes.index') }}" class="nav-sublink">
                            <i class="fas fa-chalkboard nav-icon"></i>ថ្នាក់រៀន
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('study-shifts.*') ? 'active' : '' }}">
                        <a href="{{ route('study-shifts.index') }}" class="nav-sublink">
                            <i class="fas fa-clock nav-icon"></i>វេនសិក្សា
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('students.*') ? 'active' : '' }}">
                        <a href="{{ route('students.index') }}" class="nav-sublink">
                            <i class="fas fa-user-graduate nav-icon"></i>សិស្ស
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                        <a href="{{ route('subjects.index') }}" class="nav-sublink">
                            <i class="fas fa-atom nav-icon"></i>មុខវិជ្ជា
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('academic-years.*') ? 'active' : '' }}">
                        <a href="{{ route('academic-years.index') }}" class="nav-sublink">
                            <i class="fas fa-calendar-alt nav-icon"></i>ឆ្នាំសិក្សា
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('grades.*') ? 'active' : '' }}">
                        <a href="{{ route('grades.index') }}" class="nav-sublink">
                            <i class="fas fa-layer-group nav-icon"></i>កម្រិតថ្នាក់
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('enrollments.*') ? 'active' : '' }}">
                        <a href="{{ route('enrollments.index') }}" class="nav-sublink">
                            <i class="fas fa-user-check nav-icon"></i>ការចុះឈ្មោះ
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('teaching.*', 'teachers.*', 'teacher-assignments.*', 'teacher-subjects.*', 'teacher-schedules.*') ? 'active' : '' }}">
                        <a href="{{ route('teaching.dashboard') }}" class="nav-sublink">
                            <i class="fas fa-chalkboard-teacher nav-icon"></i>គ្រូ និងកាលវិភាគ
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ ហិរញ្ញវត្ថុ ============ --}}
            <li class="nav-section-header">
                <span>ហិរញ្ញវត្ថុ</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('finance.*', 'payments.*', 'expenses.*', 'accounting.*') ? 'open' : '' }}">
                <a href="#financeMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-money-bill-wave nav-icon"></i>
                    <span class="nav-text">ហិរញ្ញវត្ថុ</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('finance.*', 'payments.*', 'expenses.*', 'accounting.*') ? 'show' : '' }}" id="financeMenu">
                    <li class="{{ request()->routeIs('payments.create') ? 'active' : '' }}">
                        <a href="{{ route('payments.create') }}" class="nav-sublink">
                            <i class="fas fa-hand-holding-dollar nav-icon"></i>ទទួលប្រាក់
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('payments.index') ? 'active' : '' }}">
                        <a href="{{ route('payments.index') }}" class="nav-sublink">
                            <i class="fas fa-history nav-icon"></i>ប្រវត្តិបង់ប្រាក់
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <a href="{{ route('expenses.index') }}" class="nav-sublink">
                            <i class="fas fa-receipt nav-icon"></i>ចំណាយ
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('accounting.*') ? 'active' : '' }}">
                        <a href="{{ route('accounting.index') }}" class="nav-sublink">
                            <i class="fas fa-calculator nav-icon"></i>គណនេយ្យ
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ ពិន្ទុ ============ --}}
            <li class="nav-section-header">
                <span>ពិន្ទុ</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('scores.*') ? 'open' : '' }}">
                <a href="#scoresMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-star-half-alt nav-icon"></i>
                    <span class="nav-text">ពិន្ទុសិស្ស</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('scores.*') ? 'show' : '' }}" id="scoresMenu">
                    <li class="{{ request()->routeIs('scores.create') ? 'active' : '' }}">
                        <a href="{{ route('scores.create') }}" class="nav-sublink">
                            <i class="fas fa-pen nav-icon"></i>បញ្ចូលពិន្ទុ
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('scores.index', 'scores.show') ? 'active' : '' }}">
                        <a href="{{ route('scores.index') }}" class="nav-sublink">
                            <i class="fas fa-tasks nav-icon"></i>គ្រប់គ្រងពិន្ទុ
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('scores.report') ? 'active' : '' }}">
                        <a href="{{ route('scores.index') }}" class="nav-sublink">
                            <i class="fas fa-chart-bar nav-icon"></i>របាយការណ៍ពិន្ទុ
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ វត្តមាន ============ --}}
            <li class="nav-section-header">
                <span>វត្តមាន</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('attendance.*') ? 'open' : '' }}">
                <a href="#attendanceMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-clipboard-check nav-icon"></i>
                    <span class="nav-text">វត្តមានសិស្ស</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('attendance.*') ? 'show' : '' }}" id="attendanceMenu">
                    <li class="{{ request()->routeIs('attendance.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('attendance.dashboard') }}" class="nav-sublink">
                            <i class="fas fa-chart-pie nav-icon"></i>ផ្ទាំងវត្តមាន
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.create') ? 'active' : '' }}">
                        <a href="{{ route('attendance.create') }}" class="nav-sublink">
                            <i class="fas fa-user-check nav-icon"></i>វត្តមានសិស្ស
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.index', 'attendance.show', 'attendance.edit') ? 'active' : '' }}">
                        <a href="{{ route('attendance.index') }}" class="nav-sublink">
                            <i class="fas fa-history nav-icon"></i>ប្រវត្តិវត្តមាន
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.absent') ? 'active' : '' }}">
                        <a href="{{ route('attendance.absent') }}" class="nav-sublink">
                            <i class="fas fa-user-xmark nav-icon"></i>បញ្ជីអវត្តមាន
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('attendance.report') ? 'active' : '' }}">
                        <a href="{{ route('attendance.report') }}" class="nav-sublink">
                            <i class="fas fa-chart-bar nav-icon"></i>របាយការណ៍វត្តមាន
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ កាតឌីជីថល ============ --}}
            <li class="nav-section-header">
                <span>កាតឌីជីថល</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('digital-cards.*') ? 'open' : '' }}">
                <a href="#idCardsMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-id-card nav-icon"></i>
                    <span class="nav-text">កាតឌីជីថល</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('digital-cards.*') ? 'show' : '' }}" id="idCardsMenu">
                    <li class="{{ request()->routeIs('digital-cards.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('digital-cards.dashboard') }}" class="nav-sublink">
                            <i class="fas fa-chart-pie nav-icon"></i>ផ្ទាំងកាតសិស្ស
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('digital-cards.create') ? 'active' : '' }}">
                        <a href="{{ route('digital-cards.create') }}" class="nav-sublink">
                            <i class="fas fa-id-card-alt nav-icon"></i>កាតសិស្ស
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('digital-cards.index', 'digital-cards.show') ? 'active' : '' }}">
                        <a href="{{ route('digital-cards.index') }}" class="nav-sublink">
                            <i class="fas fa-address-card nav-icon"></i>បញ្ជីកាតសិស្ស
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ របាយការណ៍ ============ --}}
            <li class="nav-section-header">
                <span>របាយការណ៍</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('reports.*') ? 'open' : '' }}">
                <a href="#reportsMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-chart-pie nav-icon"></i>
                    <span class="nav-text">របាយការណ៍</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsMenu">
                    <li class="{{ request()->routeIs('reports.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('reports.dashboard') }}" class="nav-sublink">
                            <i class="fas fa-chart-pie nav-icon"></i>ផ្ទាំងរបាយការណ៍
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('reports.show', 'payments') }}" class="nav-sublink">
                            <i class="fas fa-coins nav-icon"></i>សាច់ប្រាក់
                        </a>
                    </li>
                    <li><a href="{{ route('reports.show', 'students') }}" class="nav-sublink">
                            <i class="fas fa-users nav-icon"></i>សិស្ស
                        </a>
                    </li>
                    <li><a href="{{ route('reports.show', 'scores') }}" class="nav-sublink">
                            <i class="fas fa-poll nav-icon"></i>ពិន្ទុ
                        </a>
                    </li>
                        <li><a href="{{ route('reports.show', 'finance') }}" class="nav-sublink">
                            <i class="fas fa-chart-line nav-icon"></i>ហិរញ្ញវត្ថុ
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ អ្នកប្រើប្រាស់ ============ --}}
            @if(auth()->user()->isAdmin())
            <li class="nav-section-header">
                <span>ការគ្រប់គ្រង</span>
            </li>

            <li class="nav-item has-submenu {{ request()->routeIs('users.*', 'staff.*') ? 'open' : '' }}">
                <a href="#usersMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-users-cog nav-icon"></i>
                    <span class="nav-text">អ្នកប្រើប្រាស់</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('users.*', 'staff.*') ? 'show' : '' }}" id="usersMenu">
                    <li class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <a href="{{ route('users.index') }}" class="nav-sublink">
                            <i class="fas fa-user nav-icon"></i>អ្នកប្រើប្រាស់
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                        <a href="{{ route('employees.index') }}" class="nav-sublink">
                            <i class="fas fa-user-tie nav-icon"></i>បុគ្គលិក
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('teachers.registration.*') ? 'active' : '' }}">
                        <a href="{{ route('teachers.registration.create') }}" class="nav-sublink">
                            <i class="fas fa-user-plus nav-icon"></i>ចុះឈ្មោះគ្រូ
                        </a>
                    </li>
                </ul>
            </li>

            {{-- ============ ការកំណត់ ============ --}}
            <li class="nav-item has-submenu {{ request()->routeIs('settings.*', 'branches.*') ? 'open' : '' }}">
                <a href="#settingsMenu" class="nav-link nav-toggle" data-bs-toggle="collapse">
                    <i class="fas fa-cog nav-icon"></i>
                    <span class="nav-text">ការកំណត់</span>
                    <i class="fas fa-chevron-right nav-arrow"></i>
                </a>
                <ul class="nav-submenu collapse {{ request()->routeIs('settings.*', 'branches.*') ? 'show' : '' }}" id="settingsMenu">
                    <li class="{{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a href="{{ route('settings.index') }}" class="nav-sublink">
                            <i class="fas fa-school nav-icon"></i>សាលា
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('settings.services.*') ? 'active' : '' }}">
                        <a href="{{ route('settings.services.index') }}" class="nav-sublink">
                            <i class="fas fa-concierge-bell nav-icon"></i>សេវាកម្ម
                        </a>
                    </li>
                    @if(auth()->user()->isSuperAdmin())
                    <li class="{{ request()->routeIs('branches.*') ? 'active' : '' }}">
                        <a href="{{ route('branches.index') }}" class="nav-sublink">
                            <i class="fas fa-code-branch nav-icon"></i>សាខា
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

        </ul>
    </div>

</nav>

{{-- Mobile backdrop --}}
<div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>
