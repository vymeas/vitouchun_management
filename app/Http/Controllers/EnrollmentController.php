<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Http\Requests\UpdateEnrollmentRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Enrollment::query()->with(['student', 'academicYear', 'grade', 'schoolClass', 'branch']);

        if (!$request->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('student', fn ($studentQuery) => $studentQuery
                ->where('name_kh', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $enrollments = $query->latest('enrollment_date')->paginate(15)->withQueryString();

        return view('enrollments.index', compact('enrollments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $selectedStudentId = (int) ($request->query('student_id') ?? $request->input('student_id') ?? 0);
        $selectedStudent = $selectedStudentId ? Student::find($selectedStudentId) : null;
        if ($selectedStudent && !$request->user()->isSuperAdmin()) {
            $this->ensureBranchAccess($request, $selectedStudent->branch_id);
        }

        $branchId = $selectedStudent?->branch_id ?? ($request->user()->isSuperAdmin() ? null : $request->user()->branch_id);
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $students = Student::forBranch($branchId)->where('status', 'active')->orderBy('name_kh')->get();
        $academicYears = AcademicYear::forBranch($branchId)->orderByDesc('start_date')->get();
        $grades = Grade::forBranch($branchId)->orderBy('level')->get();
        $classes = SchoolClass::forBranch($branchId)->with(['academicYear', 'grade'])->orderBy('name')->get();

        return view('enrollments.create', compact('branches', 'students', 'academicYears', 'grades', 'classes', 'selectedStudentId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);
        $schoolClass = SchoolClass::withCount(['students as active_students' => fn ($query) => $query->where('status', 'active')])->findOrFail($data['class_id']);

        $duplicateEnrollment = Enrollment::where('student_id', $student->id)
            ->where('academic_year_id', $data['academic_year_id'])
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        abort_if($duplicateEnrollment, 422, 'សិស្សនេះមានការចុះឈ្មោះសម្រាប់ឆ្នាំសិក្សានេះរួចហើយ។');

        $this->ensureBranchAccess($request, $student->branch_id);
        if ((int) $schoolClass->branch_id !== (int) $student->branch_id) {
            throw ValidationException::withMessages([
                'class_id' => sprintf(
                    'សាខារបស់សិស្ស (%s) និងថ្នាក់ (%s) មិនត្រូវគ្នាទេ។ សូមជ្រើសរើសថ្នាក់ក្នុងសាខារបស់សិស្ស។',
                    $student->branch?->name_kh ?: $student->branch?->name ?: 'មិនស្គាល់',
                    $schoolClass->branch?->name_kh ?: $schoolClass->branch?->name ?: 'មិនស្គាល់',
                ),
            ]);
        }
        abort_if($schoolClass->active_students >= $schoolClass->capacity, 422, 'ថ្នាក់នេះពេញហើយ។');

        $data['branch_id'] = $student->branch_id;
        $data['status'] = $data['status'] ?? 'pending';
        $enrollment = DB::transaction(function () use ($data, $student) {
            $enrollment = Enrollment::create($data);
            AuditService::log('enrollment.created', 'ចុះឈ្មោះសិស្ស: ' . $student->name_kh, $enrollment, [], $enrollment->toArray());
            return $enrollment;
        });

        if ($request->boolean('save_and_pay')) {
            return redirect()->route('payments.create', ['student_id' => $enrollment->student_id, 'enrollment_id' => $enrollment->id])->with('success', 'បានចុះឈ្មោះសិស្សដោយជោគជ័យ។');
        }

        return redirect()->route('enrollments.show', $enrollment)->with('success', 'បានចុះឈ្មោះសិស្សដោយជោគជ័យ។');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enrollment $enrollment)
    {
        $this->ensureModelBranchAccess($enrollment);
        $enrollment->load(['student', 'academicYear', 'grade', 'schoolClass', 'branch']);

        return view('enrollments.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enrollment $enrollment)
    {
        $this->ensureModelBranchAccess($enrollment);

        return view('enrollments.edit', compact('enrollment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnrollmentRequest $request, Enrollment $enrollment)
    {
        $this->ensureModelBranchAccess($enrollment);
        $old = $enrollment->toArray();
        $enrollment->update($request->validated());
        AuditService::log('enrollment.updated', 'កែប្រែស្ថានភាពការចុះឈ្មោះ', $enrollment, $old, $enrollment->fresh()->toArray());

        return redirect()->route('enrollments.show', $enrollment)->with('success', 'បានកែប្រែការចុះឈ្មោះដោយជោគជ័យ។');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enrollment $enrollment)
    {
        $this->ensureModelBranchAccess($enrollment);
        abort_if($enrollment->status === 'completed', 422, 'មិនអាចលុបប្រវត្តិការចុះឈ្មោះដែលបានបញ្ចប់ទេ។');
        $old = $enrollment->toArray();
        $enrollment->update(['status' => 'cancelled']);
        AuditService::log('enrollment.cancelled', 'បោះបង់ការចុះឈ្មោះ', $enrollment, $old, $enrollment->fresh()->toArray());

        return redirect()->route('enrollments.index')->with('success', 'បានបោះបង់ការចុះឈ្មោះដោយជោគជ័យ។');
    }

    private function ensureBranchAccess(Request $request, int $branchId): void
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->branch_id === $branchId, 403);
    }

    private function ensureModelBranchAccess(Enrollment $enrollment): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $enrollment->branch_id, 403);
    }
}
