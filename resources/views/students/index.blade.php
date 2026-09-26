​@extends('layouts.app')

@section('title', 'សិស្ស')
@section('page-title', 'សិស្ស')

@section('content')
<div class="page-header">
    <div>
        <h2 class="page-header-title"><i class="fas fa-user-graduate text-primary me-2"></i>សិស្ស</h2>
        <p class="page-header-subtitle">{{ $students->total() }} សិស្ស</p>
    </div>
    <a href="{{ route('students.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតសិស្ស</a>
</div>

<div class="filter-bar mb-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3"><label class="form-label">ស្វែងរក</label><input type="text" name="search" class="form-control" placeholder="កូដ ឬឈ្មោះសិស្ស..." value="{{ request('search') }}"></div>
        @if(auth()->user()->isSuperAdmin())
            <div class="col-md-2"><label class="form-label">សាខា</label><select name="branch_id" class="form-select"><option value="all">គ្រប់សាខា</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" {{ (string) request('branch_id', 'all') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>@endforeach</select></div>
        @endif
        <div class="col-md-2"><label class="form-label">កម្រិតថ្នាក់</label><select name="grade_id" class="form-select"><option value="">គ្រប់កម្រិត</option>@foreach($grades as $grade)<option value="{{ $grade->id }}" {{ request('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">ថ្នាក់រៀន</label><select name="class_id" class="form-select"><option value="">គ្រប់ថ្នាក់</option>@foreach($classes as $class)<option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">គ្រូបន្ទុក</label><select name="teacher_id" class="form-select"><option value="">គ្រប់គ្រូ</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>{{ $teacher->display_name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">ភេទ</label><select name="gender" class="form-select"><option value="">គ្រប់ភេទ</option><option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>ប្រុស</option><option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>ស្រី</option></select></div>
        <div class="col-md-2"><label class="form-label">ប្រភេទសិស្ស</label><select name="student_type" class="form-select"><option value="">គ្រប់ប្រភេទ</option>@foreach($studentTypes as $studentType)<option value="{{ $studentType }}" {{ request('student_type') === $studentType ? 'selected' : '' }}>{{ $studentType }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">ស្ថានភាព</label><select name="status" class="form-select"><option value="">គ្រប់សភាព</option><option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>សកម្ម</option><option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>អសកម្ម</option></select></div>
        <div class="col-auto d-flex gap-2"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i>តម្រង</button><a href="{{ route('students.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i>សម្អាត Filter</a></div>
    </form>
</div>

<div class="card table-card">
    <div class="table-container">
        <table class="table table-hover align-middle mb-0 table-fluid">
            <colgroup>
                <col style="width: 5%">
                <col style="width: 15%">
                <col style="width: 6%">
                <col style="width: 9%">
                <col style="width: 13%">
                <col style="width: 14%">
                <col style="width: 11%">
                <col style="width: 10%">
                <col style="width: 17%">
            </colgroup>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ឈ្មោះ</th>
                    <th>ភេទ</th>
                    <th>ថ្ងៃកំណើត</th>
                    <th>ទំនាក់ទំនងបន្ទាន់</th>
                    <th>ការចុះឈ្មោះ</th>
                    <th>គ្រូបន្ទុក</th>
                    <th>ស្ថានភាព</th>
                    <th class="text-end">សកម្មភាព</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                @php
                    $currentEnrollment = $student->enrollments->first();
                    $dateOfBirth = $student->date_of_birth;
                    $currentStatus = $currentEnrollment?->status ?? $student->status;
                @endphp
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($students->currentPage() - 1) * $students->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($student->photo)
                                <img src="{{ asset('storage/' . $student->photo) }}" alt="Student photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                            @else
                                <div class="d-flex align-items-center justify-content-center border rounded-circle bg-light" style="width:40px;height:40px;flex-shrink:0;">
                                    <i class="fas fa-user text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $student->khmer_name ?? $student->name_kh }}</div>
                                <span class="badge bg-light text-dark border fw-normal font-monospace">{{ $student->student_code ?? $student->code }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        @switch(strtolower((string) $student->gender))
                            @case('m')
                            @case('male')
                                ប្រុស
                                @break
                            @case('f')
                            @case('female')
                                ស្រី
                                @break
                            @default
                                —
                        @endswitch
                    </td>
                    <td class="text-nowrap">
                        <div>{{ $dateOfBirth?->format('d/m/Y') ?? '—' }}</div>
                        <small class="text-muted">{{ $dateOfBirth ? (int) $dateOfBirth->diffInYears(now()) . ' ឆ្នាំ' : '—' }}</small>
                    </td>
                    <td>
                        @if($student->emergency_contact_name || $student->emergency_contact_phone)
                            <div>{{ $student->emergency_contact_name ?: '—' }}</div>
                            <small class="text-muted">{{ $student->emergency_contact_phone ?: '—' }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div>{{ $currentEnrollment?->schoolClass?->name ?? '—' }}</div>
                        <small class="text-muted">{{ $currentEnrollment?->academicYear?->name ?? '—' }}</small>
                    </td>
                    <td>{{ $currentEnrollment?->schoolClass?->teacher?->display_name ?? '—' }}</td>
                    <td>
                        @switch($currentStatus)
                            @case('active')
                                <span class="badge badge-active">កំពុងសិក្សា</span>
                                @break
                            @case('pending')
                                <span class="badge bg-warning text-dark">រង់ចាំ</span>
                                @break
                            @case('completed')
                                <span class="badge bg-secondary">បញ្ចប់</span>
                                @break
                            @case('transferred')
                                <span class="badge bg-info text-dark">ផ្ទេរ</span>
                                @break
                            @default
                                <span class="badge badge-inactive">អសកម្ម</span>
                        @endswitch
                    </td>
                    <td class="text-end">
                        <div class="d-flex flex-wrap gap-1 justify-content-end action-btn-group">
                            <a href="{{ route('students.show', $student) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('students.edit', $student) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                            <a href="{{ route('payments.create', ['student_id' => $student->id]) }}" class="btn btn-sm btn-outline-info" title="បង់ប្រាក់"><i class="fas fa-cash-register"></i></a>

                            @if($currentStatus === 'active')
                                <form action="{{ route('students.suspend', $student) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់ផ្អាកការសិក្សារបស់សិស្សនេះមែនទេ?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="ផ្អាកការសិក្សា"><i class="fas fa-pause"></i></button>
                                </form>
                            @else
                                <form action="{{ route('students.resume', $student) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់បើកការសិក្សារបស់សិស្សនេះវិញមែនទេ?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="បើកការសិក្សាវិញ"><i class="fas fa-play"></i></button>
                                </form>
                            @endif
                            <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('តើអ្នកពិតជាចង់លុបសិស្សនេះមែនទេ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9"><div class="empty-state"><i class="fas fa-user-graduate"></i><h5>មិនមានសិស្ស</h5><p>ចុចប៊ូតុងខាងលើដើម្បីបង្កើតថ្មី</p></div></td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="card-body border-top py-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">បង្ហាញ {{ $students->firstItem() }}–{{ $students->lastItem() }} ក្នុង {{ $students->total() }} សិស្ស</div>
            {{ $students->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
