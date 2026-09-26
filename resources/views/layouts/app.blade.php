<!DOCTYPE html>
<html lang="km" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }}</title>

    {{-- Google Fonts: Noto Sans Khmer + Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    {{-- Custom App CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="app-body">

    {{-- Sidebar --}}
    @include('layouts.partials.sidebar')

    {{-- Main wrapper --}}
    <div class="main-wrapper" id="mainWrapper">

        {{-- Top Navbar --}}
        @include('layouts.partials.navbar')

        {{-- Page Content --}}
        <main class="main-content">
            <div class="container-fluid py-4">

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm rounded-3" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>មានបញ្ហាជាមួយទិន្នន័យ៖</strong>
                    <ul class="mb-0 mt-1">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                {{-- Page Content --}}
                @yield('content')

            </div>
        </main>

        {{-- Footer --}}
        <footer class="main-footer">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    &copy; {{ date('Y') }} {{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }}
                </span>
                <span class="text-muted small">
                    Laravel {{ app()->version() }}
                </span>
            </div>
        </footer>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggler = document.getElementById('sidebarToggler');
        const wrapper = document.getElementById('mainWrapper');
        const sidebar = document.getElementById('appSidebar');

        if (toggler) {
            toggler.addEventListener('click', function() {
                sidebar.classList.toggle('sidebar-collapsed');
                wrapper.classList.toggle('main-expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('sidebar-collapsed') ? '1' : '0');
            });
        }

        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            sidebar?.classList.add('sidebar-collapsed');
            wrapper?.classList.add('main-expanded');
        }

        // Auto-dismiss alerts
        setTimeout(() => {
            document.querySelectorAll('.alert.show').forEach(el => {
                bootstrap.Alert.getOrCreateInstance(el).close();
            });
        }, 6000);
    });
    </script>

    @stack('scripts')
</body>
</html>
