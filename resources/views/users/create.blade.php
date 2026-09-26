@extends('layouts.app')

@section('title', 'បង្កើតអ្នកប្រើប្រាស់ថ្មី')
@section('page-title', 'បង្កើតអ្នកប្រើប្រាស់ថ្មី')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-user-plus text-primary me-2"></i>បង្កើតអ្នកប្រើប្រាស់ថ្មី
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">អ្នកប្រើប្រាស់</a></li>
                <li class="breadcrumb-item active" aria-current="page">បង្កើតថ្មី</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i>ត្រលប់ក្រោយ
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                ព័ត៌មានគណនី
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="username" class="form-control font-monospace @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label">ពាក្យសម្ងាត់ <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="full_name" class="form-label">ឈ្មោះពេញ (English) <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name') }}" required>
                            @error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="full_name_kh" class="form-label">ឈ្មោះពេញ (ខ្មែរ)</label>
                            <input type="text" name="full_name_kh" id="full_name_kh" class="form-control @error('full_name_kh') is-invalid @enderror" value="{{ old('full_name_kh') }}">
                            @error('full_name_kh')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">អ៊ីម៉ែល</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">លេខទូរស័ព្ទ</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="role" class="form-label">តួនាទី <span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="">-- ជ្រើសរើស --</option>
                                @foreach($roles as $key => $label)
                                    <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label">សភាព <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>សកម្ម</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>អសកម្ម</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        @if(auth()->user()->isSuperAdmin())
                        <div class="col-md-4">
                            <label for="branch_id" class="form-label">សាខា</label>
                            <select name="branch_id" id="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                                <option value="">-- គ្រប់សាខា --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name_kh ?: $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif

                    </div>

                    <div class="mt-4 border-top pt-3 text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>រក្សាទុកទិន្នន័យ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
