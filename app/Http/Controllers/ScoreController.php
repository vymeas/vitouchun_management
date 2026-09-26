<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScoresRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\SchoolClass;
use App\Models\Score;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Services\AuditService;
use App\Services\ScoreCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends Controller
{
    public function index(Request $request)
    {
        $query = Score::with(['student', 'subject', 'schoolClass', 'academicYear', 'term']);
        if (!$request->user()->isSuperAdmin()) $query->where('branch_id', $request->user()->branch_id);
        if ($search = $request->input('search')) $query->whereHas('student', fn ($q) => $q->where('name_kh', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        foreach (['academic_year_id', 'class_id', 'subject_id', 'term_id', 'result'] as $filter) if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        $scores = $query->latest()->paginate(25)->withQueryString();
        return view('scores.index', compact('scores'));
    }

    public function create(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $academicYears = AcademicYear::forBranch($branchId)->orderByDesc('start_date')->get();
        $classes = SchoolClass::forBranch($branchId)->with(['grade', 'academicYear'])->orderBy('name')->get();
        $terms = Term::forBranch($branchId)->where('status', 'active')->with(['academicYear', 'grade'])->orderBy('name')->get();
        $selectedClass = $request->filled('class_id') ? SchoolClass::forBranch($branchId)->with('grade')->find($request->integer('class_id')) : null;
        $subjects = Subject::forBranch($branchId)->with('parent')->active()
            ->when($selectedClass, fn ($query) => $query->where(function ($subjectQuery) use ($selectedClass) {
                $subjectQuery->whereHas('grades', fn ($gradeQuery) => $gradeQuery->where('grades.id', $selectedClass->grade_id)->where('grade_subjects.status', 'active'))
                    ->orWhereDoesntHave('grades');
            }))
            ->orderBy('display_order')->orderBy('name_kh')->get();
        $students = $selectedClass ? Student::whereIn('id', $selectedClass->students()->where('status', 'active')->pluck('student_id'))->orderBy('name_kh')->get() : collect();
        return view('scores.create', compact('academicYears', 'classes', 'subjects', 'terms', 'selectedClass', 'students'));
    }

    public function store(StoreScoresRequest $request)
    {
        $data = $request->validated();
        $schoolClass = SchoolClass::findOrFail($data['class_id']);
        $subject = Subject::findOrFail($data['subject_id']);
        $term = Term::findOrFail($data['term_id']);
        $this->authorizeBranch($schoolClass->branch_id);
        abort_unless($schoolClass->branch_id === $subject->branch_id && $schoolClass->branch_id === $term->branch_id, 422, 'ទិន្នន័យសាខាមិនត្រូវគ្នាទេ។');
        abort_unless((int) $schoolClass->academic_year_id === (int) $data['academic_year_id'] && (int) $term->academic_year_id === (int) $data['academic_year_id'], 422, 'ឆ្នាំសិក្សាមិនត្រូវគ្នាទេ។');
        abort_if($subject->status !== 'active', 422, 'មុខវិជ្ជានេះត្រូវបានដាក់ជាអសកម្ម។');
        if ($subject->grades()->exists()) {
            abort_unless($subject->grades()->whereKey($schoolClass->grade_id)->wherePivot('status', 'active')->exists(), 422, 'មុខវិជ្ជានេះមិនត្រូវបានកំណត់សម្រាប់ថ្នាក់នេះទេ។');
        }
        $enrolledStudentIds = $schoolClass->students()->where('status', 'active')->pluck('student_id')->all();
        $maximum = (float) $term->maximum_score;

        DB::transaction(function () use ($data, $schoolClass, $subject, $term, $enrolledStudentIds, $maximum, $request) {
            foreach ($data['scores'] as $row) {
                if ($row['score'] === null || $row['score'] === '') continue;
                abort_unless(in_array((int) $row['student_id'], $enrolledStudentIds, true), 422, 'សិស្សមិនស្ថិតក្នុងថ្នាក់នេះទេ។');
                $score = (float) $row['score'];
                abort_if($score > $maximum, 422, 'ពិន្ទុលើសពិន្ទុអតិបរមា។');
                $calculated = ScoreCalculationService::grade($score, $maximum);
                Score::updateOrCreate(
                    ['student_id' => $row['student_id'], 'subject_id' => $subject->id, 'academic_year_id' => $data['academic_year_id'], 'term_id' => $term->id],
                    ['class_id' => $schoolClass->id, 'branch_id' => $schoolClass->branch_id, 'score' => $score, 'maximum_score' => $maximum, 'grade' => $calculated['grade'], 'result' => $calculated['result'], 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]
                );
            }
            AuditService::log('scores.bulk_saved', 'បញ្ចូលពិន្ទុមុខវិជ្ជា: ' . $subject->name_kh, $subject, [], ['class_id' => $schoolClass->id, 'term_id' => $term->id]);
        });

        return redirect()->route('scores.index')->with('success', 'បានរក្សាទុកពិន្ទុដោយជោគជ័យ។');
    }

    public function show(Score $score)
    {
        $this->authorizeBranch($score->branch_id);
        $score->load(['student', 'subject', 'schoolClass', 'academicYear', 'term']);
        return view('scores.show', compact('score'));
    }

    public function destroy(Score $score)
    {
        $this->authorizeBranch($score->branch_id);
        $old = $score->toArray();
        $score->delete();
        AuditService::log('score.deleted', 'លុបពិន្ទុសិស្ស', $score, $old);
        return redirect()->route('scores.index')->with('success', 'បានលុបពិន្ទុដោយជោគជ័យ។');
    }

    private function authorizeBranch(int $branchId): void
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId, 403);
    }
}
