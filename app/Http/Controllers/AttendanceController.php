<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\AttendanceCalculationService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $today = Attendance::forBranch($branchId)->whereDate('attendance_date', today())->get();
        $week = Attendance::forBranch($branchId)->whereBetween('attendance_date', [now()->startOfWeek(), now()->endOfWeek()])->get();
        $month = Attendance::forBranch($branchId)->whereBetween('attendance_date', [now()->startOfMonth(), now()->endOfMonth()])->get();
        $summary = AttendanceCalculationService::summary($today);
        $totalStudents = Student::forBranch($branchId)->where('status', 'active')->count();
        return view('attendance.dashboard', compact('summary', 'week', 'month', 'totalStudents'));
    }

    public function index(Request $request)
    {
        $query = $this->scopedQuery($request)->with(['student', 'schoolClass', 'term', 'marker']);
        $this->filters($query, $request);
        $attendances = $query->latest('attendance_date')->paginate(25)->withQueryString();
        return view('attendance.index', compact('attendances'));
    }

    public function create(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $academicYears = AcademicYear::forBranch($branchId)->orderByDesc('start_date')->get();
        $terms = Term::forBranch($branchId)->where('status', 'active')->with('academicYear')->orderBy('name')->get();
        $classes = SchoolClass::forBranch($branchId)->with('grade')->orderBy('name')->get();
        $selectedClass = $request->filled('class_id') ? SchoolClass::forBranch($branchId)->find($request->integer('class_id')) : null;
        $date = $request->date('attendance_date') ?? today();
        $students = $selectedClass ? Student::whereIn('id', $selectedClass->students()->where('status', 'active')->pluck('student_id'))->orderBy('name_kh')->get() : collect();
        $existing = $selectedClass && $request->filled('academic_year_id') && $request->filled('term_id')
            ? Attendance::where('class_id', $selectedClass->id)->whereDate('attendance_date', $date)->where('academic_year_id', $request->integer('academic_year_id'))->where('term_id', $request->integer('term_id'))->get()->keyBy('student_id')
            : collect();
        return view('attendance.create', compact('academicYears', 'terms', 'classes', 'selectedClass', 'students', 'existing', 'date'));
    }

    public function store(StoreAttendanceRequest $request)
    {
        $data = $request->validated();
        $class = SchoolClass::findOrFail($data['class_id']);
        $this->authorizeBranch($class->branch_id);
        $term = Term::findOrFail($data['term_id']);
        abort_unless($term->branch_id === $class->branch_id && (int) $term->academic_year_id === (int) $data['academic_year_id'], 422, 'Term និងថ្នាក់មិនត្រូវគ្នាទេ។');
        $enrollmentMap = $class->students()->where('academic_year_id', $data['academic_year_id'])->where('status', 'active')->get()->keyBy('student_id');

        DB::transaction(function () use ($data, $class, $enrollmentMap, $request) {
            foreach ($data['records'] as $record) {
                $enrollment = $enrollmentMap->get($record['student_id']);
                abort_unless($enrollment, 422, 'សិស្សមិនស្ថិតក្នុងថ្នាក់ ឬឆ្នាំសិក្សានេះទេ។');
                $attendance = Attendance::query()
                    ->where('student_id', $record['student_id'])
                    ->where('class_id', $class->id)
                    ->whereDate('attendance_date', $data['attendance_date'])
                    ->where('academic_year_id', $data['academic_year_id'])
                    ->where('term_id', $data['term_id'])
                    ->first();
                $attributes = ['branch_id' => $class->branch_id, 'enrollment_id' => $enrollment->id, 'status' => $record['status'], 'check_in_time' => $record['check_in_time'] ?? null, 'check_out_time' => $record['check_out_time'] ?? null, 'reason' => $record['reason'] ?? null, 'note' => $record['note'] ?? null, 'marked_by' => $request->user()->id, 'updated_by' => $request->user()->id];
                if ($attendance) {
                    $attendance->update($attributes);
                } else {
                    Attendance::create([...$attributes, 'student_id' => $record['student_id'], 'class_id' => $class->id, 'attendance_date' => $data['attendance_date'], 'academic_year_id' => $data['academic_year_id'], 'term_id' => $data['term_id']]);
                }
            }
            AuditService::log('attendance.bulk_saved', 'រក្សាទុកវត្តមានថ្នាក់: ' . $class->name, $class, [], ['date' => $data['attendance_date'], 'count' => count($data['records'])]);
        });
        return redirect()->route('attendance.index')->with('success', 'បានរក្សាទុកវត្តមានដោយជោគជ័យ។');
    }

    public function show(Attendance $attendance)
    {
        $this->authorizeBranch($attendance->branch_id);
        $attendance->load(['student', 'schoolClass', 'academicYear', 'term', 'enrollment', 'marker', 'updater']);
        return view('attendance.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        $this->authorizeBranch($attendance->branch_id);
        return view('attendance.edit', compact('attendance'));
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->authorizeBranch($attendance->branch_id);
        $old = $attendance->toArray();
        $attendance->update([...$request->validated(), 'updated_by' => $request->user()->id]);
        AuditService::log('attendance.updated', 'កែប្រែវត្តមានសិស្ស', $attendance, $old, $attendance->fresh()->toArray());
        return redirect()->route('attendance.show', $attendance)->with('success', 'បានកែប្រែវត្តមានដោយជោគជ័យ។');
    }

    public function destroy(Attendance $attendance)
    {
        $this->authorizeBranch($attendance->branch_id);
        $old = $attendance->toArray();
        $attendance->delete();
        AuditService::log('attendance.deleted', 'លុបកំណត់ត្រាវត្តមាន', $attendance, $old);
        return redirect()->route('attendance.index')->with('success', 'បានលុបកំណត់ត្រាវត្តមាន។');
    }

    public function absent(Request $request)
    {
        $request->merge(['status' => 'absent,excused']);
        $query = $this->scopedQuery($request)->with(['student', 'schoolClass', 'marker'])->whereIn('status', ['absent', 'excused']);
        $this->filters($query, $request);
        $attendances = $query->latest('attendance_date')->paginate(25)->withQueryString();
        return view('attendance.absent', compact('attendances'));
    }

    public function report(Request $request)
    {
        $query = $this->scopedQuery($request);
        $this->filters($query, $request);
        $records = $query->get();
        $summary = AttendanceCalculationService::summary($records);
        return view('attendance.report', compact('records', 'summary'));
    }

    private function scopedQuery(Request $request)
    {
        return Attendance::query()->forBranch($request->user()->isSuperAdmin() ? null : $request->user()->branch_id);
    }

    private function filters($query, Request $request): void
    {
        foreach (['academic_year_id', 'term_id', 'class_id', 'student_id', 'status'] as $filter) {
            if ($request->filled($filter) && $filter !== 'status') $query->where($filter, $request->input($filter));
        }
        if ($request->filled('from')) $query->whereDate('attendance_date', '>=', $request->date('from'));
        if ($request->filled('to')) $query->whereDate('attendance_date', '<=', $request->date('to'));
        if ($request->filled('search')) $query->whereHas('student', fn ($q) => $q->where('name_kh', 'like', '%' . $request->input('search') . '%')->orWhere('code', 'like', '%' . $request->input('search') . '%'));
    }

    private function authorizeBranch(int $branchId): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId, 403);
    }
}
