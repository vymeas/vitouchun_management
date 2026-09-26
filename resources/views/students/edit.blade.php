@extends('layouts.app')

@section('title', 'កែប្រែសិស្ស')
@section('page-title', 'កែប្រែសិស្ស')

@section('content')
<div class="card mx-auto" style="max-width: 1200px;">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>កែប្រែសិស្ស</h5>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('students.update', $student) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-user-graduate me-2"></i>ព័ត៌មានសិស្ស</h6>
                <div class="row g-3">
                    @if(auth()->user()->isSuperAdmin())
                        <div class="col-lg-4">
                            <label class="form-label">សាខា <span class="text-danger">*</span></label>
                            <select name="branch_id" class="form-select" required>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ old('branch_id', $student->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name_kh ?: $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-lg-4">
                        <label class="form-label">កូដសិស្ស</label>
                        <input type="text" class="form-control" value="{{ old('student_code', $student->student_code ?? $student->code) }}" readonly>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">ឈ្មោះជាភាសាខ្មែរ <span class="text-danger">*</span></label>
                        <input type="text" name="khmer_name" class="form-control" value="{{ old('khmer_name', $student->khmer_name ?? $student->name_kh) }}" required>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">ឈ្មោះជាភាសាអង់គ្លេស <span class="text-danger">*</span></label>
                        <input type="text" name="english_name" class="form-control" value="{{ old('english_name', $student->english_name ?? $student->name_en) }}" required>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">ភេទ <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select" required>
                            <option value="">-- ជ្រើសរើស --</option>
                            <option value="M" {{ old('gender', $student->gender) == 'M' ? 'selected' : '' }}>ប្រុស</option>
                            <option value="F" {{ old('gender', $student->gender) == 'F' ? 'selected' : '' }}>ស្រី</option>
                        </select>
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">ថ្ងៃខែឆ្នាំកំណើត</label>
                        <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d') ?? $student->dob?->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                    </div>

                    <div class="col-lg-4">
                        <label class="form-label">ប្រភេទសិស្ស <span class="text-danger">*</span></label>
                        <select name="student_type" class="form-select" required>
                            <option value="">-- ជ្រើសរើស --</option>
                            <option value="សិស្សធម្មតា" {{ old('student_type', $student->student_type) == 'សិស្សធម្មតា' ? 'selected' : '' }}>សិស្សធម្មតា</option>
                            <option value="សិស្សអាហារូបករណ៍" {{ old('student_type', $student->student_type) == 'សិស្សអាហារូបករណ៍' ? 'selected' : '' }}>សិស្សអាហារូបករណ៍</option>
                            <option value="សិស្សផ្ទេរចូល" {{ old('student_type', $student->student_type) == 'សិស្សផ្ទេរចូល' ? 'selected' : '' }}>សិស្សផ្ទេរចូល</option>
                            <option value="សិស្សផ្សេងទៀត" {{ old('student_type', $student->student_type) == 'សិស្សផ្សេងទៀត' ? 'selected' : '' }}>សិស្សផ្សេងទៀត</option>
                        </select>
                    </div>

                    <div class="col-lg-8">
                        <label class="form-label">ទីកន្លែងកំណើត</label>
                        <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth', $student->place_of_birth) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">អាស័យដ្ឋានបច្ចុប្បន្ន</label>
                        <textarea name="current_address" class="form-control" rows="3">{{ old('current_address', $student->current_address ?? $student->address) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-male me-2"></i>ព័ត៌មានឪពុក</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ឈ្មោះឪពុក</label>
                        <input type="text" name="father_name" class="form-control" value="{{ old('father_name', $student->father_name ?? $student->parent_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">មុខរបរ</label>
                        <input type="text" name="father_occupation" class="form-control" value="{{ old('father_occupation', $student->father_occupation ?? $student->parent_occupation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">លេខទូរស័ព្ទ</label>
                        <input type="text" name="father_phone" class="form-control" value="{{ old('father_phone', $student->father_phone ?? $student->parent_phone) }}">
                    </div>
                </div>
            </div>

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-female me-2"></i>ព័ត៌មានម្តាយ</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ឈ្មោះម្តាយ</label>
                        <input type="text" name="mother_name" class="form-control" value="{{ old('mother_name', $student->mother_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">មុខរបរ</label>
                        <input type="text" name="mother_occupation" class="form-control" value="{{ old('mother_occupation', $student->mother_occupation) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">លេខទូរស័ព្ទ</label>
                        <input type="text" name="mother_phone" class="form-control" value="{{ old('mother_phone', $student->mother_phone) }}">
                    </div>
                </div>
            </div>

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-phone me-2"></i>ទំនាក់ទំនងបន្ទាន់</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ឈ្មោះ</label>
                        <input type="text" name="emergency_contact_name" class="form-control" value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">លេខទូរស័ព្ទ</label>
                        <input type="text" name="emergency_contact_phone" class="form-control" value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}">
                    </div>
                </div>
            </div>

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-heart me-2"></i>ព័ត៌មានបន្ថែម</h6>
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">សុខភាព</label>
                        <textarea name="health_condition" class="form-control" rows="4">{{ old('health_condition', $student->health_condition) }}</textarea>
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">ចរិកលក្ខណៈ</label>
                        <textarea name="characteristics" class="form-control" rows="4">{{ old('characteristics', $student->characteristics) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="mb-4 border rounded p-3 bg-light-subtle">
                <h6 class="mb-3 text-primary"><i class="fas fa-image me-2"></i>រូបថតសិស្ស</h6>
                <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <div class="student-photo-preview" id="student-photo-preview">
                        @if($student->photo)
                            <img src="{{ asset('storage/' . $student->photo) }}" alt="Student photo" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">
                        @else
                            <i class="fas fa-user fa-3x text-muted"></i>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" id="photo-input" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">បោះបង់</a>
                <button type="submit" class="btn btn-success">រក្សាទុក</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('photo-input');
        const preview = document.getElementById('student-photo-preview');

        input?.addEventListener('change', function (event) {
            const file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Student photo preview" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">';
            };
            reader.readAsDataURL(file);
        });
    });
</script>
@endsection
