<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StudyShift;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StudyShiftController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $shifts = StudyShift::with('branch')->forBranch($branchId)->orderBy('start_time')->paginate(15);
        return view('study-shifts.index', compact('shifts'));
    }

    public function create()
    {
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : Branch::whereKey(auth()->user()->branch_id)->get();
        return view('study-shifts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        if (!$request->user()->isSuperAdmin()) $data['branch_id'] = $request->user()->branch_id;
        $shift = StudyShift::create($data);
        AuditService::log('study_shift.created', 'បង្កើតវេនសិក្សា: ' . $shift->name, $shift, [], $shift->toArray());
        return redirect()->route('study-shifts.index')->with('success', 'បានបង្កើតវេនសិក្សាដោយជោគជ័យ។');
    }

    public function edit(StudyShift $studyShift)
    {
        $this->authorizeBranch($studyShift->branch_id);
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : Branch::whereKey(auth()->user()->branch_id)->get();
        return view('study-shifts.edit', compact('studyShift', 'branches'));
    }

    public function update(Request $request, StudyShift $studyShift)
    {
        $this->authorizeBranch($studyShift->branch_id);
        $data = $this->validated($request);
        if (!$request->user()->isSuperAdmin()) $data['branch_id'] = $request->user()->branch_id;
        $studyShift->update($data);
        return redirect()->route('study-shifts.index')->with('success', 'បានកែប្រែវេនសិក្សាដោយជោគជ័យ។');
    }

    public function destroy(StudyShift $studyShift)
    {
        $this->authorizeBranch($studyShift->branch_id);
        abort_if($studyShift->classes()->exists(), 422, 'មិនអាចលុបវេនដែលកំពុងប្រើដោយថ្នាក់រៀនបានទេ។');
        $studyShift->delete();
        return redirect()->route('study-shifts.index')->with('success', 'បានលុបវេនសិក្សាដោយជោគជ័យ។');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    private function authorizeBranch(int $branchId): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId, 403);
    }
}