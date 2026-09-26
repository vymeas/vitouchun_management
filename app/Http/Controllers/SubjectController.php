<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Branch;
use App\Models\Grade;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $query = Subject::query()->with(['branch', 'parent', 'children', 'grades' => fn ($gradeQuery) => $gradeQuery->orderBy('level')])->forBranch($branchId);
        if ($search = $request->input('search')) $query->where(fn ($q) => $q->where('name_kh', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        if ($request->filled('level')) $query->where('level', $request->input('level'));
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('type')) $query->where('subject_type', $request->input('type'));
        if ($request->filled('grade_id')) $query->whereHas('grades', fn ($gradeQuery) => $gradeQuery->where('grades.id', $request->integer('grade_id')));
        $subjects = $query->orderByRaw('COALESCE(parent_subject_id, id)')->orderByRaw('parent_subject_id IS NOT NULL')->orderBy('display_order')->orderBy('name_kh')->paginate(15)->withQueryString();
        $grades = Grade::forBranch($branchId)->orderBy('level')->get();
        return view('subjects.index', compact('subjects', 'grades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $branchId = auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id;
        $grades = Grade::forBranch($branchId)->orderBy('level')->get();
        $parentSubjects = Subject::forBranch($branchId)->where('level', 'preschool')->whereNull('parent_subject_id')->where('status', 'active')->orderBy('display_order')->orderBy('name_kh')->get();
        return view('subjects.create', compact('branches', 'grades', 'parentSubjects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSubjectRequest $request)
    {
        $data = $request->validated();
        $branchId = $request->user()->isSuperAdmin() ? (int) ($data['branch_id'] ?? 0) : (int) $request->user()->branch_id;
        abort_unless($branchId > 0, 422, 'សូមជ្រើសរើសសាខា។');
        $gradeIds = $data['grade_ids'] ?? [];
        unset($data['grade_ids']);
        $this->validateParent($data['parent_subject_id'] ?? null, $data['level'], $branchId);
        $this->validateUniqueName($data['name_kh'], $data['level'], $branchId, $data['parent_subject_id'] ?? null);
        $this->validateGrades($gradeIds, $data['level'], $branchId);
        $subject = DB::transaction(function () use ($data, $branchId, $gradeIds) {
            $data['branch_id'] = $branchId;
            $data['code'] = $this->nextCode($branchId);
            $subject = Subject::create($data);
            $subject->grades()->sync($this->gradeSyncData($gradeIds));
            return $subject;
        });
        AuditService::log('subject.created', 'បង្កើតមុខវិជ្ជា: ' . $subject->name_kh, $subject, [], $subject->toArray());
        return redirect()->route('subjects.index')->with('success', 'បានបង្កើតមុខវិជ្ជាដោយជោគជ័យ។');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subject $subject)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $subject->branch_id, 403);
        $subject->load(['branch', 'parent', 'children', 'grades']);
        return view('subjects.show', compact('subject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subject $subject)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $subject->branch_id, 403);
        $branches = auth()->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $branchId = auth()->user()->isSuperAdmin() ? null : auth()->user()->branch_id;
        $grades = Grade::forBranch($branchId)->orderBy('level')->get();
        $parentSubjects = Subject::forBranch($branchId)->where('level', 'preschool')->whereNull('parent_subject_id')->where('id', '!=', $subject->id)->where('status', 'active')->orderBy('display_order')->orderBy('name_kh')->get();
        $subject->load(['parent', 'grades']);
        return view('subjects.edit', compact('subject', 'branches', 'grades', 'parentSubjects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->branch_id === $subject->branch_id, 403);
        $data = $request->validated();
        if (!$request->user()->isSuperAdmin()) $data['branch_id'] = $request->user()->branch_id;
        $gradeIds = $data['grade_ids'] ?? [];
        unset($data['grade_ids'], $data['code']);
        $this->validateParent($data['parent_subject_id'] ?? null, $data['level'], (int) $subject->branch_id, $subject->id);
        $this->validateUniqueName($data['name_kh'], $data['level'], (int) $subject->branch_id, $data['parent_subject_id'] ?? null, $subject->id);
        $this->validateGrades($gradeIds, $data['level'], (int) $subject->branch_id);
        $old = $subject->toArray();
        DB::transaction(function () use ($subject, $data, $gradeIds) {
            $subject->update($data);
            $subject->grades()->sync($this->gradeSyncData($gradeIds));
        });
        AuditService::log('subject.updated', 'កែប្រែមុខវិជ្ជា: ' . $subject->name_kh, $subject, $old, $subject->fresh()->toArray());
        return redirect()->route('subjects.index')->with('success', 'បានកែប្រែមុខវិជ្ជាដោយជោគជ័យ។');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subject $subject)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $subject->branch_id, 403);
        $old = $subject->toArray();
        $subject->update(['status' => 'inactive']);
        AuditService::log('subject.deleted', 'លុបមុខវិជ្ជា: ' . $subject->name_kh, $subject, $old);
        return redirect()->route('subjects.index')->with('success', 'បានលុបមុខវិជ្ជាដោយជោគជ័យ។');
    }

    private function validateGrades(array $gradeIds, string $level, int $branchId): void
    {
        if (!$gradeIds) {
            return;
        }

        $valid = Grade::whereIn('id', $gradeIds)
            ->where('branch_id', $branchId)
            ->where(function ($query) use ($level) {
                $query->where('education_level', $level)->orWhereNull('education_level');
            })
            ->count();

        abort_unless($valid === count(array_unique(array_map('intval', $gradeIds))), 422, 'ថ្នាក់ដែលបានជ្រើសរើសមិនត្រូវនឹងកម្រិតមុខវិជ្ជា។');
    }

    private function validateParent(?int $parentId, string $level, int $branchId, ?int $subjectId = null): void
    {
        if (!$parentId) {
            return;
        }

        abort_unless($level === 'preschool', 422, 'មុខវិជ្ជាបឋមសិក្សាមិនអាចមាន Parent Subject ទេ។');
        abort_unless($subjectId === null || $parentId !== $subjectId, 422, 'មុខវិជ្ជាមិនអាចធ្វើជា Parent របស់ខ្លួនឯងទេ។');

        $parent = Subject::whereKey($parentId)->where('branch_id', $branchId)->first();
        abort_unless($parent && $parent->level === 'preschool' && $parent->parent_subject_id === null && $parent->status === 'active', 422, 'Parent Subject មិនត្រឹមត្រូវសម្រាប់កម្រិតនេះទេ។');
    }

    private function validateUniqueName(string $name, string $level, int $branchId, ?int $parentId, ?int $subjectId = null): void
    {
        $query = Subject::where('branch_id', $branchId)->where('level', $level)->where('name_kh', $name);
        $parentId ? $query->where('parent_subject_id', $parentId) : $query->whereNull('parent_subject_id');
        if ($subjectId) $query->where('id', '!=', $subjectId);
        abort_if($query->exists(), 422, 'ឈ្មោះមុខវិជ្ជានេះមានរួចហើយសម្រាប់ Parent នេះ។');
    }

    private function gradeSyncData(array $gradeIds): array
    {
        $sync = [];
        foreach (array_values(array_unique(array_map('intval', $gradeIds))) as $order => $gradeId) {
            $sync[$gradeId] = ['display_order' => $order, 'status' => 'active'];
        }
        return $sync;
    }

    private function nextCode(int $branchId): string
    {
        Branch::whereKey($branchId)->lockForUpdate()->firstOrFail();
        $last = Subject::where('branch_id', $branchId)->where('code', 'like', 'SUB%')->orderByDesc('id')->lockForUpdate()->first();
        $number = 1;
        if ($last && preg_match('/(\d+)$/', (string) $last->code, $matches)) {
            $number = (int) $matches[1] + 1;
        }
        return 'SUB' . str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
