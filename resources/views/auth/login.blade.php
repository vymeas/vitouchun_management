<!DOCTYPE html>
<html lang="km" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ចូលប្រើប្រាស់ប្រព័ន្ធ — {{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }}</title>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Khmer:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- Bootstrap 5 & FontAwesome --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    {{-- Custom CSS --}}
    @vite(['resources/css/app.css'])

    <style>
        body {
            background-color: var(--content-bg);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: none;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .login-logo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .login-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.4;
        }
        .login-subtitle {
            font-size: 0.85rem;
            opacity: 0.8;
            font-family: var(--font-latin);
            margin-top: 4px;
        }
        .login-body {
            padding: 30px;
            background: white;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="login-header">
        <div class="login-logo">
            <img src="{{ asset('storage/img/logo.png') }}"
                alt="{{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }} Logo">
        </div>
            <h1 class="login-title">{{ \App\Services\SettingService::get('school_name_kh', 'សាលារៀនវិទូជន') }}</h1>
            <div class="login-subtitle">Vitouchun School</div>
        </div>
        
        <div class="login-body">
            
            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger mb-4 rounded-3 p-3">
                    <div class="d-flex gap-2">
                        <i class="fas fa-exclamation-circle mt-1"></i>
                        <ul class="mb-0 ps-3 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error == 'auth.failed' ? 'ឈ្មោះអ្នកប្រើ ឬពាក្យសម្ងាត់មិនត្រឹមត្រូវទេ។' : $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Username or Email --}}
                <div class="mb-3">
                    <label for="login" class="form-label">ឈ្មោះអ្នកប្រើប្រាស់ ឬ អ៊ីម៉ែល (Username / Email)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at text-muted"></i></span>
                        <input type="text" id="login" name="login" class="form-control form-control-lg fs-6" 
                               value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="បញ្ចូលឈ្មោះអ្នកប្រើ ឬ អ៊ីម៉ែល">
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="form-label">ពាក្យសម្ងាត់ (Password)</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" id="password" name="password" class="form-control form-control-lg fs-6" 
                               required autocomplete="current-password" placeholder="បញ្ចូលពាក្យសម្ងាត់">
                    </div>
                </div>

                {{-- Remember Me & Forgot Password --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            ចងចាំគណនីខ្ញុំ
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary w-100 btn-lg fs-6 py-2">
                    <i class="fas fa-sign-in-alt me-2"></i>ចូលប្រើប្រាស់
                </button>
            </form>
        </div>
    </div>

</body>
</html>
