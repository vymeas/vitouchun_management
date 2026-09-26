<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicYear::query()->with('branch');

        if (!$request->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $academicYears = $query->orderByDesc('start_date')->paginate(15)->withQueryString();
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();

        return view('academic-years.index', compact('academicYears', 'branches'));
    }

    public function create()
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('academic-years.create', compact('branches'));
    }

    public function store(StoreAcademicYearRequest $request)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        if (!empty($data['is_active'])) {
            AcademicYear::where('branch_id', $data['branch_id'])->where('id', '!=', $request->route('academic_year')?->id ?? 0)->update(['is_active' => false]);
        }

        $academicYear = AcademicYear::create($data);

        AuditService::log('academic_year.created', 'បង្កើតឆ្នាំសិក្សា: ' . $academicYear->name, $academicYear, [], $academicYear->toArray());

        return redirect()->route('academic-years.index')->with('success', 'បានបង្កើតឆ្នាំសិក្សាដោយជោគជ័យ។');
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->load('branch');

        return view('academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear)
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('academic-years.edit', compact('academicYear', 'branches'));
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        if (!empty($data['is_active'])) {
            AcademicYear::where('branch_id', $data['branch_id'])->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $old = $academicYear->toArray();
        $academicYear->update($data);

        AuditService::log('academic_year.updated', 'កែប្រែឆ្នាំសិក្សា: ' . $academicYear->name, $academicYear, $old, $academicYear->fresh()->toArray());

        return redirect()->route('academic-years.index')->with('success', 'បានកែប្រែឆ្នាំសិក្សាដោយជោគជ័យ។');
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        AuditService::log('academic_year.deleted', 'លុបឆ្នាំសិក្សា: ' . $academicYear->name, $academicYear, $academicYear->toArray());

        return redirect()->route('academic-years.index')->with('success', 'បានលុបឆ្នាំសិក្សាដោយជោគជ័យ។');
    }
}
