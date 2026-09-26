<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $accessibleBranchId = $isSuperAdmin ? null : (int) $request->user()->branch_id;
        $selectedBranchId = $accessibleBranchId;
        if ($isSuperAdmin && $request->filled('branch_id') && $request->input('branch_id') !== 'all') {
            $selectedBranchId = (int) $request->input('branch_id');
            abort_unless(Branch::active()->whereKey($selectedBranchId)->exists(), 422, 'សាខាដែលបានជ្រើសរើសមិនត្រឹមត្រូវទេ។');
        }

        $query = Student::query()->with([
            'branch',
            'enrollments' => fn ($enrollmentQuery) => $enrollmentQuery
                ->whereIn('status', ['active', 'pending'])
                ->with([
                    'schoolClass.teacher',
                    'academicYear',
                    'payments' => fn ($paymentQuery) => $paymentQuery
                        ->where('payments.status', 'posted')
                        ->whereHas('enrollment', fn ($enrollmentQuery) => $enrollmentQuery
                            ->whereColumn('enrollments.branch_id', 'payments.branch_id')
                            ->whereColumn('enrollments.academic_year_id', 'payments.academic_year_id'))
                        ->with(['items', 'service'])
                        ->latest('payment_date')
                        ->latest('id'),
                ])
                ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                ->latest('enrollment_date'),
        ]);

        if ($selectedBranchId) $query->where('branch_id', $selectedBranchId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('student_code', 'like', "%{$search}%")
                  ->orWhere('name_kh', 'like', "%{$search}%")
                  ->orWhere('khmer_name', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('english_name', 'like', "%{$search}%")
                  ->orWhere('father_phone', 'like', "%{$search}%")
                  ->orWhere('mother_phone', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('emergency_contact_name', 'like', "%{$search}%")
                  ->orWhere('emergency_contact_phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('gender')) $query->where('gender', $request->input('gender'));
        if ($request->filled('student_type')) $query->where('student_type', $request->input('student_type'));
        if ($request->filled('grade_id')) $query->whereHas('enrollments', fn ($enrollmentQuery) => $enrollmentQuery->whereIn('status', ['active', 'pending'])->where('grade_id', $request->integer('grade_id')));
        if ($request->filled('class_id')) $query->whereHas('enrollments', fn ($enrollmentQuery) => $enrollmentQuery->whereIn('status', ['active', 'pending'])->where('class_id', $request->integer('class_id')));
        if ($request->filled('teacher_id')) $query->whereHas('enrollments.schoolClass', fn ($classQuery) => $classQuery->where('teacher_id', $request->integer('teacher_id')));

        $students = $query->orderBy('name_kh')->paginate(15)->withQueryString();
        $branches = $isSuperAdmin ? Branch::active()->orderBy('name')->get() : collect();
        $grades = Grade::forBranch($selectedBranchId)->orderBy('level')->get();
        $classes = SchoolClass::forBranch($selectedBranchId)->with('grade')->orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->where('status', 'active')->forBranch($selectedBranchId)->orderBy('full_name_kh')->get();
        $studentTypes = Student::forBranch($selectedBranchId)->whereNotNull('student_type')->distinct()->orderBy('student_type')->pluck('student_type');

        return view('students.index', compact('students', 'branches', 'grades', 'classes', 'teachers', 'studentTypes', 'selectedBranchId'));
    }

    public function create()
    {
        abort_unless($this->canManageStudents(auth()->user()), 403, 'អ្នកមិនមានសិទ្ធិបង្កើតសិស្សទេ។');

        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('students.create', compact('branches'));
    }

    public function store(StoreStudentRequest $request)
    {
        abort_unless($this->canManageStudents($request->user()), 403, 'អ្នកមិនមានសិទ្ធិបង្កើតសិស្សទេ។');

        $data = $request->validated();
        $branchId = $request->user()->isSuperAdmin() ? (int) ($data['branch_id'] ?? 0) : (int) $request->user()->branch_id;
        abort_unless($branchId > 0, 422, 'សូមជ្រើសរើសសាខា។');

        $student = DB::transaction(function () use ($data, $request, $branchId) {
            $data['branch_id'] = $branchId;
            $data['created_by'] = $request->user()->id;
            $data['student_code'] = $this->generateStudentCode($branchId);
            $data['code'] = $data['student_code'];
            $data['khmer_name'] = $data['khmer_name'] ?? $data['name_kh'] ?? null;
            $data['english_name'] = $data['english_name'] ?? $data['name_en'] ?? null;
            $data['name_kh'] = $data['khmer_name'];
            $data['name_en'] = $data['english_name'];
            $data['date_of_birth'] = $data['date_of_birth'] ?? $data['dob'] ?? null;
            $data['dob'] = $data['date_of_birth'];
            $data['current_address'] = $data['current_address'] ?? $data['address'] ?? null;
            $data['address'] = $data['current_address'];
            $data['father_name'] = $data['father_name'] ?? $data['parent_name'] ?? null;
            $data['parent_name'] = $data['father_name'];
            $data['father_phone'] = $data['father_phone'] ?? $data['parent_phone'] ?? null;
            $data['parent_phone'] = $data['father_phone'];

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('students', 'public');
                $data['photo'] = $path;
            }

            return Student::create($data);
        });

        AuditService::log('student.created', 'បង្កើតសិស្ស: ' . ($student->khmer_name ?? $student->name_kh), $student, [], $student->toArray());

        if ($request->boolean('save_and_pay')) {
            return redirect()->route('payments.create', ['student_id' => $student->id])->with('success', 'សិស្សត្រូវបានបង្កើតដោយជោគជ័យ។');
        }

        return redirect()->route('students.show', $student)->with('success', 'សិស្សត្រូវបានបង្កើតដោយជោគជ័យ។');
    }

    public function show(Student $student)
    {
        $student->load('branch', 'createdBy');

        return view('students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        abort_unless($this->canManageStudents(auth()->user()), 403, 'អ្នកមិនមានសិទ្ធិកែប្រែសិស្សទេ។');

        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('students.edit', compact('student', 'branches'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        abort_unless($this->canManageStudents($request->user()), 403, 'អ្នកមិនមានសិទ្ធិកែប្រែសិស្សទេ។');

        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $data['khmer_name'] = $data['khmer_name'] ?? $data['name_kh'] ?? $student->khmer_name;
        $data['english_name'] = $data['english_name'] ?? $data['name_en'] ?? $student->english_name;
        $data['name_kh'] = $data['khmer_name'];
        $data['name_en'] = $data['english_name'];
        $data['date_of_birth'] = $data['date_of_birth'] ?? $data['dob'] ?? $student->date_of_birth;
        $data['dob'] = $data['date_of_birth'];
        $data['current_address'] = $data['current_address'] ?? $data['address'] ?? $student->current_address;
        $data['address'] = $data['current_address'];
        $data['student_code'] = $student->student_code ?? $student->code;
        $data['code'] = $data['student_code'];

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }

            $path = $request->file('photo')->store('students', 'public');
            $data['photo'] = $path;
        }

        $old = $student->toArray();
        $student->update($data);
        AuditService::log('student.updated', 'កែប្រែសិស្ស: ' . ($student->khmer_name ?? $student->name_kh), $student, $old, $student->fresh()->toArray());

        return redirect()->route('students.show', $student)->with('success', 'បានកែប្រែសិស្សដោយជោគជ័យ។');
    }

    public function destroy(Student $student)
    {
        abort_unless($this->canManageStudents(auth()->user()), 403, 'អ្នកមិនមានសិទ្ធិលុបសិស្សទេ។');

        $student->update(['status' => 'inactive']);
        AuditService::log('student.deleted', 'លុបសិស្ស: ' . ($student->khmer_name ?? $student->name_kh), $student, $student->toArray());

        return redirect()->route('students.index')->with('success', 'បានលុបសិស្សដោយជោគជ័យ។');
    }

    public function suspend(Student $student)
    {
        abort_unless($this->canManageStudents(auth()->user()), 403, 'អ្នកមិនមានសិទ្ធិផ្អាកការសិក្សាសិស្សទេ។');
        abort_unless(auth()->user()->isSuperAdmin() || $student->branch_id === auth()->user()->branch_id, 403);

        DB::transaction(function () use ($student) {
            $old = $student->toArray();
            $student->update(['status' => 'inactive']);
            $student->enrollments()->where('status', 'active')->update(['status' => 'suspended']);
            AuditService::log('student.suspended', 'ផ្អាកការសិក្សាសិស្ស: ' . ($student->khmer_name ?? $student->name_kh), $student, $old, $student->fresh()->toArray());
        });

        return redirect()->route('students.index')->with('success', 'បានផ្អាកការសិក្សាសិស្សដោយជោគជ័យ។');
    }

    public function resume(Student $student)
    {
        abort_unless($this->canManageStudents(auth()->user()), 403, 'អ្នកមិនមានសិទ្ធិបើកការសិក្សាសិស្សវិញទេ។');
        abort_unless(auth()->user()->isSuperAdmin() || $student->branch_id === auth()->user()->branch_id, 403);

        DB::transaction(function () use ($student) {
            $old = $student->toArray();
            $student->update(['status' => 'active']);
            $student->enrollments()->where('status', 'suspended')->update(['status' => 'active']);
            AuditService::log('student.resumed', 'បើកការសិក្សាសិស្សវិញ: ' . ($student->khmer_name ?? $student->name_kh), $student, $old, $student->fresh()->toArray());
        });

        return redirect()->route('students.index')->with('success', 'បានបើកការសិក្សាសិស្សវិញដោយជោគជ័យ។');
    }

    private function canManageStudents($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($user->role, ['admin', 'registrar'], true)) {
            return true;
        }

        return $user->can('students.create')
            || $user->can('students.edit')
            || $user->can('students.delete')
            || $user->can('academic.view');
    }

    private function generateStudentCode(int $branchId): string
    {
        return DB::transaction(function () use ($branchId) {
            $prefix = 'Stu';

            $lastStudent = Student::where('branch_id', $branchId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $lastCode = $lastStudent?->student_code ?? $lastStudent?->code ?? null;
            $number = 1;

            if ($lastCode) {
                $match = preg_match('/(\d+)$/', $lastCode, $matches);
                if ($match) {
                    $number = (int) $matches[1] + 1;
                }
            }

            return $prefix . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
        });
    }
}
