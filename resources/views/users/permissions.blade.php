@extends('layouts.app')

@section('title', 'សិទ្ធិអ្នកប្រើប្រាស់')
@section('page-title', 'សិទ្ធិអ្នកប្រើប្រាស់')

@section('content')

<div class="page-header">
    <div>
        <h2 class="page-header-title">
            <i class="fas fa-key text-warning me-2"></i>កំណត់សិទ្ធិ៖ {{ $user->full_name_kh ?: $user->full_name }}
        </h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}">អ្នកប្រើប្រាស់</a></li>
                <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->username }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">សិទ្ធិ (Permissions)</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>ត្រលប់ក្រោយ
        </a>
    </div>
</div>

<form action="{{ route('users.permissions.save', $user) }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-12 mb-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll(true)">
                <i class="fas fa-check-square"></i> ជ្រើសរើសទាំងអស់
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">
                <i class="fas fa-square"></i> ដកទាំងអស់
            </button>
        </div>

        @foreach($allPermissions as $module => $permissions)
        <div class="col-md-6 col-xl-4 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="text-uppercase fw-bold text-secondary">{{ $module }}</span>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input module-toggler" type="checkbox" data-module="{{ Str::slug($module) }}" onchange="toggleModule(this)">
                    </div>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush module-{{ Str::slug($module) }}">
                        @foreach($permissions as $perm)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <label class="form-check-label flex-grow-1" for="perm_{{ $perm->id }}">
                                {{ $perm->name }}
                                <div class="small text-muted font-monospace">{{ $perm->action }}</div>
                            </label>
                            <div class="form-check m-0">
                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[{{ $perm->id }}]" value="1" id="perm_{{ $perm->id }}" {{ in_array($perm->id, $userPermissions) ? 'checked' : '' }}>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="position-sticky bottom-0 bg-white p-3 border-top shadow-sm mt-4 d-flex justify-content-end gap-2" style="z-index: 10;">
        <button type="submit" class="btn btn-primary px-5">
            <i class="fas fa-save me-2"></i>រក្សាទុកសិទ្ធិ
        </button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function selectAll(state) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
        document.querySelectorAll('.module-toggler').forEach(cb => cb.checked = state);
    }

    function toggleModule(toggler) {
        const moduleClass = '.module-' + toggler.dataset.module;
        document.querySelectorAll(moduleClass + ' .perm-checkbox').forEach(cb => {
            cb.checked = toggler.checked;
        });
    }

    // Auto-check module togglers if all children are checked
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.module-toggler').forEach(toggler => {
            const moduleClass = '.module-' + toggler.dataset.module;
            const checkboxes = document.querySelectorAll(moduleClass + ' .perm-checkbox');
            if (checkboxes.length > 0) {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                toggler.checked = allChecked;
            }
        });
    });
</script>
@endpush
