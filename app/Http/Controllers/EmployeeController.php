<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Employee::forBranch($this->branchId($request));
        return view('employees.dashboard', ['total' => (clone $query)->count(), 'active' => (clone $query)->where('status','active')->count(), 'inactive' => (clone $query)->whereIn('status',['inactive','suspended','terminated','resigned','retired'])->count(), 'leave' => (clone $query)->where('status','on_leave')->count(), 'male' => (clone $query)->where('gender','M')->count(), 'female' => (clone $query)->where('gender','F')->count()]);
    }

    public function index(Request $request)
    {
        $query = Employee::with(['branch','department','position'])->forBranch($this->branchId($request));
        if ($search = $request->input('search')) $query->where(fn ($q) => $q->where('employee_id','like',"%{$search}%")->orWhere('name_kh','like',"%{$search}%")->orWhere('name_en','like',"%{$search}%")->orWhere('phone','like',"%{$search}%")->orWhere('email','like',"%{$search}%"));
        foreach (['branch_id','department_id','position_id','gender','status'] as $filter) if ($request->filled($filter) && ($filter === 'branch_id' ? auth()->user()->isSuperAdmin() : true)) $query->where($filter, $request->input($filter));
        $employees = $query->latest()->paginate((int) $request->input('per_page', 15))->withQueryString();
        $branchId = $this->branchId($request);
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $departments = Department::forBranch($branchId)->where('is_active', true)->orderBy('name_kh')->get();
        $positions = Position::forBranch($branchId)->where('is_active', true)->orderBy('name_kh')->get();
        return view('employees.index', compact('employees','branches','departments','positions'));
    }

    public function create(Request $request)
    {
        $branchId = $this->branchId($request);
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $departments = Department::forBranch($branchId)->where('is_active',true)->orderBy('name_kh')->get();
        $positions = Position::forBranch($branchId)->where('is_active',true)->orderBy('name_kh')->get();
        return view('employees.create', compact('branches','departments','positions'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->isSuperAdmin() ? ($data['branch_id'] ?? null) : auth()->user()->branch_id;
        abort_unless($data['branch_id'], 422, 'សូមជ្រើសរើសសាខា។');
        $this->validateRelatedBranch($data);
        if ($request->hasFile('photo')) $data['photo'] = $request->file('photo')->store('employees','public');
        $data['employee_id'] = $this->nextEmployeeId();
        $employee = Employee::create($data);
        AuditService::log('employee.created','បង្កើតបុគ្គលិក: '.$employee->employee_id,$employee,[], $employee->toArray());
        return redirect()->route('employees.show',$employee)->with('success','បានបង្កើតបុគ្គលិកដោយជោគជ័យ។');
    }

    public function show(Employee $employee)
    {
        $this->authorizeBranch($employee->branch_id);
        $employee->load(['branch','department','position','user']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee, Request $request)
    {
        $this->authorizeBranch($employee->branch_id);
        $branchId = $this->branchId($request);
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $departments = Department::forBranch($branchId)->where('is_active',true)->orderBy('name_kh')->get();
        $positions = Position::forBranch($branchId)->where('is_active',true)->orderBy('name_kh')->get();
        return view('employees.edit', compact('employee','branches','departments','positions'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->authorizeBranch($employee->branch_id);
        $data = $request->validated();
        $data['branch_id'] = auth()->user()->isSuperAdmin() ? ($data['branch_id'] ?? $employee->branch_id) : auth()->user()->branch_id;
        $this->validateRelatedBranch($data);
        if ($request->hasFile('photo')) { if ($employee->photo) Storage::disk('public')->delete($employee->photo); $data['photo'] = $request->file('photo')->store('employees','public'); }
        $old = $employee->toArray(); $employee->update($data);
        AuditService::log('employee.updated','កែប្រែបុគ្គលិក: '.$employee->employee_id,$employee,$old,$employee->fresh()->toArray());
        return redirect()->route('employees.show',$employee)->with('success','បានកែប្រែបុគ្គលិកដោយជោគជ័យ។');
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeBranch($employee->branch_id); $old = $employee->toArray(); $employee->delete();
        AuditService::log('employee.deactivated','បិទបុគ្គលិក: '.$employee->employee_id,$employee,$old);
        return redirect()->route('employees.index')->with('success','បានបិទបុគ្គលិកដោយជោគជ័យ។');
    }

    public function export(Request $request)
    {
        $query = Employee::with(['branch','department','position'])->forBranch($this->branchId($request));
        if ($request->filled('status')) $query->where('status',$request->input('status')); if ($request->filled('department_id')) $query->where('department_id',$request->input('department_id')); if ($request->filled('search')) $query->where('name_kh','like','%'.$request->input('search').'%');
        $employees = $query->cursor();
        return response()->streamDownload(function () use ($employees) { $handle=fopen('php://output','w'); fputcsv($handle,['Employee ID','Name KH','Name EN','Gender','Phone','Branch','Department','Position','Joining Date','Salary','Status']); foreach($employees as $e) fputcsv($handle,[$e->employee_id,$e->name_kh,$e->name_en,$e->gender,$e->phone,$e->branch?->name,$e->department?->name_en ?: $e->department?->name_kh,$e->position?->name_en ?: $e->position?->name_kh,$e->joining_date?->format('Y-m-d'),$e->basic_salary,$e->status]); fclose($handle); },'employees.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    private function branchId(Request $request): ?int { return $request->user()->isSuperAdmin() ? null : $request->user()->branch_id; }
    private function authorizeBranch(int $branchId): void { abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId,403); }
    private function validateRelatedBranch(array $data): void { if (!empty($data['department_id'])) abort_unless(Department::where('id',$data['department_id'])->where('branch_id',$data['branch_id'])->exists(),422,'ផ្នែកមិនស្ថិតក្នុងសាខានេះទេ។'); if (!empty($data['position_id'])) abort_unless(Position::where('id',$data['position_id'])->where('branch_id',$data['branch_id'])->exists(),422,'តួនាទីមិនស្ថិតក្នុងសាខានេះទេ។'); }
    private function nextEmployeeId(): string { return sprintf('EMP-%05d', Employee::withTrashed()->count()+1); }
}
