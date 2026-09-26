@extends('layouts.app')

@section('title', 'កែប្រែសាខា')
@section('page-title', 'កែប្រែសាខា')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-edit text-primary me-2"></i>កែប្រែសាខា៖ {{ $branch->name_kh ?: $branch->name }}
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('branches.index') }}">គ្រប់គ្រងសាខា</a></li>
                <li class="breadcrumb-item"><a href="{{ route('branches.show', $branch) }}">{{ $branch->code }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">កែប្រែ</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('branches.show', $branch) }}" class="btn btn-outline-primary">
            <i class="fas fa-eye"></i>មើល
        </a>
        <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>ត្រលប់ក្រោយ
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                ព័ត៌មានសាខា
            </div>
            <div class="card-body">
                <form action="{{ route('branches.update', $branch) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- Code --}}
                        <div class="col-md-4">
                            <label for="code" class="form-label">លេខកូដសាខា <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="code" class="form-control text-uppercase @error('code') is-invalid @enderror" value="{{ old('code', $branch->code) }}" required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-4">
                            <label for="status" class="form-label">សភាព <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $branch->status) == 'active' ? 'selected' : '' }}>សកម្ម</option>
                                <option value="inactive" {{ old('status', $branch->status) == 'inactive' ? 'selected' : '' }}>អសកម្ម</option>
                                <option value="closed" {{ old('status', $branch->status) == 'closed' ? 'selected' : '' }}>បិទ</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Opened At --}}
                        <div class="col-md-4">
                            <label for="opened_at" class="form-label">ថ្ងៃបើកដំណើរការ</label>
                            <input type="date" name="opened_at" id="opened_at" class="form-control @error('opened_at') is-invalid @enderror" value="{{ old('opened_at', $branch->opened_at?->format('Y-m-d')) }}">
                            @error('opened_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Name KH --}}
                        <div class="col-md-6">
                            <label for="name_kh" class="form-label">ឈ្មោះសាខា (ខ្មែរ)</label>
                            <input type="text" name="name_kh" id="name_kh" class="form-control @error('name_kh') is-invalid @enderror" value="{{ old('name_kh', $branch->name_kh) }}">
                            @error('name_kh')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Name EN --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label">ឈ្មោះសាខា (English) <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $branch->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label for="phone" class="form-label">លេខទូរស័ព្ទ</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $branch->phone) }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6">
                            <label for="email" class="form-label">អ៊ីម៉ែល</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $branch->email) }}">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div class="col-12">
                            <label for="address" class="form-label">អាសយដ្ឋាន</label>
                            <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            បានបង្កើត៖ {{ $branch->created_at->format('d/m/Y H:i') }}
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>រក្សាទុកការកែប្រែ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
