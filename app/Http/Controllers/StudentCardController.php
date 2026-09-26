<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentCardRequest;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\StudentCard;
use App\Services\AuditService;
use App\Services\StudentCardQrService;
use App\Services\StudentCardStatusService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentCardController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = StudentCard::forBranch($request->user()->isSuperAdmin() ? null : $request->user()->branch_id);
        $cards = (clone $query)->get();
        foreach ($cards as $card) StudentCardStatusService::sync($card);
        $counts = $cards->groupBy(fn ($card) => StudentCardStatusService::current($card))->map->count();
        $students = Student::forBranch($request->user()->isSuperAdmin() ? null : $request->user()->branch_id)->where('status', 'active')->count();
        return view('student-cards.dashboard', ['students' => $students, 'active' => $counts['active'] ?? 0, 'expired' => $counts['expired'] ?? 0, 'inactive' => ($counts['inactive'] ?? 0) + ($counts['lost'] ?? 0) + ($counts['replaced'] ?? 0), 'expiring' => (clone $query)->where('status', 'active')->whereBetween('expiry_date', [today(), today()->addDays(30)])->count()]);
    }

    public function index(Request $request)
    {
        $query = StudentCard::with(['student', 'enrollment.schoolClass', 'academicYear'])->forBranch($request->user()->isSuperAdmin() ? null : $request->user()->branch_id);
        if ($search = $request->input('search')) $query->where(fn ($q) => $q->where('card_number', 'like', "%{$search}%")->orWhereHas('student', fn ($s) => $s->where('name_kh', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")));
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        $cards = $query->latest()->paginate(15)->withQueryString();
        foreach ($cards as $card) StudentCardStatusService::sync($card);
        return view('student-cards.index', compact('cards'));
    }

    public function create(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $students = Student::forBranch($branchId)->where('status', 'active')->orderBy('name_kh')->get();
        $years = AcademicYear::forBranch($branchId)->orderByDesc('start_date')->get();
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        return view('student-cards.create', compact('students', 'years', 'branches'));
    }

    public function store(StoreStudentCardRequest $request)
    {
        $data = $request->validated();
        $student = Student::findOrFail($data['student_id']);
        $enrollment = Enrollment::with(['student', 'academicYear'])->findOrFail($data['enrollment_id']);
        abort_unless($enrollment->student_id === $student->id && $enrollment->academic_year_id === (int) $data['academic_year_id'] && $enrollment->branch_id === $student->branch_id, 422, 'ការចុះឈ្មោះមិនត្រឹមត្រូវសម្រាប់សិស្សនេះទេ។');
        $this->authorizeBranch($student->branch_id);
        abort_if(StudentCard::where('student_id', $student->id)->where('academic_year_id', $data['academic_year_id'])->where('status', 'active')->exists(), 422, 'សិស្សនេះមានកាតសកម្មសម្រាប់ឆ្នាំសិក្សានេះរួចហើយ។');
        $card = DB::transaction(function () use ($data, $student, $enrollment, $request) {
            $card = StudentCard::create([...$data, 'branch_id' => $student->branch_id, 'card_number' => $this->nextNumber(), 'qr_token' => Str::random(64), 'created_by' => $request->user()->id, 'status' => 'active']);
            AuditService::log('student_card.created', 'បង្កើតកាតសិស្ស: ' . $card->card_number, $card, [], $card->toArray());
            return $card;
        });
        return redirect()->route('digital-cards.show', $card)->with('success', 'បានបង្កើតកាតសិស្សដោយជោគជ័យ។');
    }

    public function show(StudentCard $digitalCard)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        StudentCardStatusService::sync($digitalCard);
        $digitalCard->load(['student', 'enrollment.schoolClass', 'enrollment.grade', 'academicYear', 'creator']);
        $qr = StudentCardQrService::svg(route('student-cards.verify', $digitalCard->qr_token));
        return view('student-cards.show', compact('digitalCard', 'qr'));
    }

    public function print(StudentCard $digitalCard)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        $digitalCard->load(['student', 'enrollment.schoolClass', 'enrollment.grade', 'academicYear']);
        $qr = StudentCardQrService::svg(route('student-cards.verify', $digitalCard->qr_token));
        return view('student-cards.print', compact('digitalCard', 'qr'));
    }

    public function pdf(StudentCard $digitalCard)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        $digitalCard->load(['student', 'enrollment.schoolClass', 'enrollment.grade', 'academicYear']);
        $qr = StudentCardQrService::svg(route('student-cards.verify', $digitalCard->qr_token));
        return Pdf::loadView('student-cards.print', compact('digitalCard', 'qr'))->setPaper('a4')->download($digitalCard->card_number . '.pdf');
    }

    public function deactivate(StudentCard $digitalCard, Request $request)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        $digitalCard->update(['status' => 'inactive', 'updated_by' => $request->user()->id]);
        AuditService::log('student_card.deactivated', 'បិទកាតសិស្ស', $digitalCard);
        return back()->with('success', 'បានបិទកាតសិស្ស។');
    }

    public function lost(StudentCard $digitalCard, Request $request)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        $digitalCard->update(['status' => 'lost', 'updated_by' => $request->user()->id]);
        AuditService::log('student_card.lost', 'រាយការណ៍កាតបាត់', $digitalCard);
        return back()->with('success', 'បានរាយការណ៍កាតបាត់។');
    }

    public function renew(StudentCard $digitalCard, Request $request)
    {
        $this->authorizeBranch($digitalCard->branch_id);
        $newCard = DB::transaction(function () use ($digitalCard, $request) {
            $digitalCard->update(['status' => 'replaced', 'updated_by' => $request->user()->id]);
            return StudentCard::create(['branch_id' => $digitalCard->branch_id, 'student_id' => $digitalCard->student_id, 'enrollment_id' => $digitalCard->enrollment_id, 'academic_year_id' => $digitalCard->academic_year_id, 'card_number' => $this->nextNumber(), 'card_type' => $digitalCard->card_type, 'issue_date' => today(), 'expiry_date' => today()->addYear(), 'status' => 'active', 'qr_token' => Str::random(64), 'created_by' => $request->user()->id]);
        });
        AuditService::log('student_card.renewed', 'បន្តសុពលភាពកាតសិស្ស', $newCard);
        return redirect()->route('digital-cards.show', $newCard)->with('success', 'បានបន្តកាតសិស្ស។');
    }

    public function replace(StudentCard $digitalCard, Request $request)
    {
        return $this->renew($digitalCard, $request);
    }

    public function verify(string $token)
    {
        $card = StudentCard::with(['student', 'enrollment.schoolClass', 'enrollment.grade', 'academicYear'])->where('qr_token', $token)->first();
        if (!$card) return view('student-cards.verify', ['card' => null, 'status' => 'invalid']);
        $status = StudentCardStatusService::current($card);
        return view('student-cards.verify', compact('card', 'status'));
    }

    private function authorizeBranch(int $branchId): void { abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $branchId, 403); }
    private function nextNumber(): string { return sprintf('VCS-%s-%06d', now()->format('Y'), StudentCard::count() + 1); }
}
