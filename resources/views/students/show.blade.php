@extends('layouts.app')

@section('title', 'ព័ត៌មានសិស្ស')
@section('page-title', 'ព័ត៌មានសិស្ស')

@section('content')
<div class="card mx-auto" style="max-width: 1100px;">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>{{ $student->khmer_name ?? $student->name_kh }}</h5>
        <div class="d-flex gap-2">
            <a href="{{ route('enrollments.create', ['student_id' => $student->id]) }}" class="btn btn-success btn-sm">ចុះឈ្មោះ</a>
            <a href="{{ route('students.edit', $student) }}" class="btn btn-primary btn-sm">កែប្រែ</a>
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary btn-sm">ត្រឡប់</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-3 text-center">
                @if($student->photo)
                    <img src="{{ asset('storage/' . $student->photo) }}" class="img-thumbnail" style="max-height: 220px; width: 100%; object-fit: cover;" alt="Student photo">
                @else
                    <div class="img-thumbnail d-flex align-items-center justify-content-center" style="height: 220px; width: 100%; background:#f8f9fa; border-radius:12px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @endif
            </div>

            <div class="col-lg-9">
                <div class="mb-4 border rounded p-3 bg-light-subtle">
                    <h6 class="mb-3 text-primary"><i class="fas fa-user-graduate me-2"></i>ព័ត៌មានសិស្ស</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>កូដសិស្ស:</strong><br>{{ $student->student_code ?? $student->code }}</div>
                        <div class="col-md-4"><strong>ឈ្មោះខ្មែរ:</strong><br>{{ $student->khmer_name ?? $student->name_kh }}</div>
                        <div class="col-md-4"><strong>ឈ្មោះអង់គ្លេស:</strong><br>{{ $student->english_name ?? $student->name_en }}</div>
                        <div class="col-md-3"><strong>ភេទ:</strong><br>{{ $student->gender == 'M' ? 'ប្រុស' : 'ស្រី' }}</div>
                        <div class="col-md-3"><strong>ថ្ងៃកំណើត:</strong><br>{{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : ($student->dob ? $student->dob->format('d/m/Y') : '—') }}</div>
                        <div class="col-md-3"><strong>ប្រភេទ:</strong><br>{{ $student->student_type ?: '—' }}</div>
                        <div class="col-md-3"><strong>ស្ថានភាព:</strong><br>{{ $student->status == 'active' ? 'សកម្ម' : 'អសកម្ម' }}</div>
                        <div class="col-md-6"><strong>ទីកន្លែងកំណើត:</strong><br>{{ $student->place_of_birth ?: '—' }}</div>
                        <div class="col-md-6"><strong>អាស័យដ្ឋានបច្ចុប្បន្ន:</strong><br>{{ $student->current_address ?: ($student->address ?: '—') }}</div>
                    </div>
                </div>

                <div class="mb-4 border rounded p-3 bg-light-subtle">
                    <h6 class="mb-3 text-primary"><i class="fas fa-male me-2"></i>ព័ត៌មានឪពុក</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>ឈ្មោះ:</strong><br>{{ $student->father_name ?: ($student->parent_name ?: '—') }}</div>
                        <div class="col-md-4"><strong>មុខរបរ:</strong><br>{{ $student->father_occupation ?: ($student->parent_occupation ?: '—') }}</div>
                        <div class="col-md-4"><strong>លេខទូរស័ព្ទ:</strong><br>{{ $student->father_phone ?: ($student->parent_phone ?: '—') }}</div>
                    </div>
                </div>

                <div class="mb-4 border rounded p-3 bg-light-subtle">
                    <h6 class="mb-3 text-primary"><i class="fas fa-female me-2"></i>ព័ត៌មានម្តាយ</h6>
                    <div class="row g-3">
                        <div class="col-md-4"><strong>ឈ្មោះ:</strong><br>{{ $student->mother_name ?: '—' }}</div>
                        <div class="col-md-4"><strong>មុខរបរ:</strong><br>{{ $student->mother_occupation ?: '—' }}</div>
                        <div class="col-md-4"><strong>លេខទូរស័ព្ទ:</strong><br>{{ $student->mother_phone ?: '—' }}</div>
                    </div>
                </div>

                <div class="mb-4 border rounded p-3 bg-light-subtle">
                    <h6 class="mb-3 text-primary"><i class="fas fa-phone me-2"></i>ទំនាក់ទំនងបន្ទាន់</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>ឈ្មោះ:</strong><br>{{ $student->emergency_contact_name ?: '—' }}</div>
                        <div class="col-md-6"><strong>លេខទូរស័ព្ទ:</strong><br>{{ $student->emergency_contact_phone ?: '—' }}</div>
                    </div>
                </div>

                <div class="mb-4 border rounded p-3 bg-light-subtle">
                    <h6 class="mb-3 text-primary"><i class="fas fa-heart me-2"></i>ព័ត៌មានបន្ថែម</h6>
                    <div class="row g-3">
                        <div class="col-md-6"><strong>សុខភាព:</strong><br>{{ $student->health_condition ?: '—' }}</div>
                        <div class="col-md-6"><strong>ចរិកលក្ខណៈ:</strong><br>{{ $student->characteristics ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
