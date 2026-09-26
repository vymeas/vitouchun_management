<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\StudyShift;
use App\Services\AuditService;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::query()->with(['academicYear', 'grade', 'teacher', 'branch']);

        if (!$request->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($academicYearId = $request->input('academic_year_id')) {
            $query->where('academic_year_id', $academicYearId);
        }

        $classes = $query->with('shift')->orderBy('name')->paginate(15)->withQueryString();
        $academicYears = AcademicYear::forBranch($request->user()->isSuperAdmin() ? null : $request->user()->branch_id)->orderByDesc('start_date')->get();
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();

        return view('classes.index', compact('classes', 'academicYears', 'branches'));
    }

    public function create()
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();
        $academicYears = AcademicYear::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->orderByDesc('start_date')->get();
        $grades = Grade::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->orderBy('level')->get();
        $teachers = User::where('role', 'teacher')->where('status', 'active')->get();
        $shifts = StudyShift::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->active()->orderBy('start_time')->get();

        return view('classes.create', compact('branches', 'academicYears', 'grades', 'teachers', 'shifts'));
    }

    public function store(StoreSchoolClassRequest $request)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $schoolClass = SchoolClass::create($data);
        AuditService::log('school_class.created', 'បង្កើតថ្នាក់: ' . $schoolClass->name, $schoolClass, [], $schoolClass->toArray());

        return redirect()->route('classes.index')->with('success', 'បានបង្កើតថ្នាក់រៀនដោយជោគជ័យ។');
    }

    public function show(SchoolClass $schoolClass)
    {
        $schoolClass->load(['academicYear', 'grade', 'teacher', 'branch']);

        return view('classes.show', compact('schoolClass'));
    }

    public function edit(SchoolClass $schoolClass)
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();
        $academicYears = AcademicYear::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->orderByDesc('start_date')->get();
        $grades = Grade::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->orderBy('level')->get();
        $teachers = User::where('role', 'teacher')->where('status', 'active')->get();
        $shifts = StudyShift::forBranch(auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id)->active()->orderBy('start_time')->get();

        return view('classes.edit', compact('schoolClass', 'branches', 'academicYears', 'grades', 'teachers', 'shifts'));
    }

    public function update(UpdateSchoolClassRequest $request, SchoolClass $schoolClass)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $old = $schoolClass->toArray();
        $schoolClass->update($data);
        AuditService::log('school_class.updated', 'កែប្រែថ្នាក់: ' . $schoolClass->name, $schoolClass, $old, $schoolClass->fresh()->toArray());

        return redirect()->route('classes.index')->with('success', 'បានកែប្រែថ្នាក់រៀនដោយជោគជ័យ។');
    }

    public function destroy(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        AuditService::log('school_class.deleted', 'លុបថ្នាក់: ' . $schoolClass->name, $schoolClass, $schoolClass->toArray());

        return redirect()->route('classes.index')->with('success', 'បានលុបថ្នាក់រៀនដោយជោគជ័យ។');
    }
}
