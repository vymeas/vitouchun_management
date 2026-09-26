<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherAssignmentRequest;
use App\Http\Requests\StoreTeacherScheduleRequest;
use App\Http\Requests\StoreTeacherSubjectRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherClassAssignment;
use App\Models\TeacherSchedule;
use App\Models\TeacherSubjectAssignment;
use App\Models\Term;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class TeachingController extends Controller
{
    public function dashboard(Request $request)
    {
        $branchId = $this->branchId($request);
        $teachers = User::where('role','teacher')->where('status','active')->forBranch($branchId)->get();
        $assignments = TeacherClassAssignment::forBranch($branchId)->where('status','active')->get();
        $subjects = TeacherSubjectAssignment::forBranch($branchId)->where('status','active')->get();
        return view('teaching.dashboard', ['teachers'=>$teachers->count(),'classes'=>$assignments->unique('class_id')->count(),'subjects'=>$subjects->unique('subject_id')->count(),'students'=>SchoolClass::forBranch($branchId)->withCount('students')->get()->sum('students_count'),'schedules'=>TeacherSchedule::forBranch($branchId)->where('status','active')->count()]);
    }

    public function teachers(Request $request)
    {
        $query = User::where('role','teacher')->with('branch')->forBranch($this->branchId($request));
        if ($search=$request->input('search')) $query->where(fn($q)=>$q->where('full_name_kh','like',"%{$search}%")->orWhere('full_name','like',"%{$search}%")->orWhere('username','like',"%{$search}%"));
        $teachers=$query->paginate(15)->withQueryString(); return view('teaching.teachers',compact('teachers'));
    }

    public function assignments(Request $request)
    {
        $branchId=$this->branchId($request); $classAssignments=TeacherClassAssignment::forBranch($branchId)->with(['teacher','schoolClass','academicYear'])->latest()->paginate(15,['*'],'class_page')->withQueryString(); $subjectAssignments=TeacherSubjectAssignment::forBranch($branchId)->with(['teacher','schoolClass','subject','academicYear','term'])->latest()->paginate(15,['*'],'subject_page')->withQueryString();
        return view('teaching.assignments',compact('classAssignments','subjectAssignments'));
    }

    public function createAssignment(Request $request)
    {
        $branchId=$this->branchId($request); $teachers=User::where('role','teacher')->where('status','active')->forBranch($branchId)->get(); $classes=SchoolClass::forBranch($branchId)->with('grade')->get(); $years=AcademicYear::forBranch($branchId)->get(); return view('teaching.assignment-create',compact('teachers','classes','years'));
    }

    public function storeAssignment(StoreTeacherAssignmentRequest $request)
    {
        $data=$request->validated(); $teacher=User::findOrFail($data['teacher_id']); $class=SchoolClass::findOrFail($data['class_id']); $this->authorizeBranch($class->branch_id); abort_unless($teacher->role==='teacher' && $teacher->branch_id===$class->branch_id,422,'គ្រូ និងថ្នាក់មិនស្ថិតក្នុងសាខាដូចគ្នាទេ។'); abort_if(TeacherClassAssignment::where($data)->exists(),422,'ការចាត់តាំងនេះមានរួចហើយ។'); $assignment=TeacherClassAssignment::create([...$data,'branch_id'=>$class->branch_id]); AuditService::log('teacher_assignment.created','ចាត់តាំងគ្រូទៅថ្នាក់',$assignment,[], $assignment->toArray()); return redirect()->route('teacher-assignments.index')->with('success','បានចាត់តាំងគ្រូដោយជោគជ័យ។');
    }

    public function createSubject(Request $request)
    {
        $branchId=$this->branchId($request); $teachers=User::where('role','teacher')->where('status','active')->forBranch($branchId)->get(); $classes=SchoolClass::forBranch($branchId)->with('grade')->get(); $subjects=Subject::forBranch($branchId)->get(); $years=AcademicYear::forBranch($branchId)->get(); $terms=Term::forBranch($branchId)->get(); return view('teaching.subject-create',compact('teachers','classes','subjects','years','terms'));
    }

    public function storeSubject(StoreTeacherSubjectRequest $request)
    {
        $data=$request->validated(); $class=SchoolClass::findOrFail($data['class_id']); $teacher=User::findOrFail($data['teacher_id']); $subject=Subject::findOrFail($data['subject_id']); $this->authorizeBranch($class->branch_id); abort_unless($teacher->role==='teacher' && $teacher->branch_id===$class->branch_id && $subject->branch_id===$class->branch_id,422,'ទិន្នន័យសាខាមិនត្រូវគ្នាទេ។'); abort_if(TeacherSubjectAssignment::where($data)->exists(),422,'ការចាត់តាំងមុខវិជ្ជានេះមានរួចហើយ។'); $assignment=TeacherSubjectAssignment::create([...$data,'branch_id'=>$class->branch_id]); AuditService::log('teacher_subject.created','ចាត់តាំងមុខវិជ្ជាឱ្យគ្រូ',$assignment,[], $assignment->toArray()); return redirect()->route('teacher-assignments.index')->with('success','បានចាត់តាំងមុខវិជ្ជាដោយជោគជ័យ។');
    }

    public function schedule(Request $request)
    {
        $schedules=TeacherSchedule::forBranch($this->branchId($request))->with(['teacher','schoolClass','subject'])->orderBy('day_of_week')->orderBy('start_time')->paginate(20); return view('teaching.schedule',compact('schedules'));
    }

    public function createSchedule(Request $request)
    {
        $branchId=$this->branchId($request); $teachers=User::where('role','teacher')->where('status','active')->forBranch($branchId)->get(); $classes=SchoolClass::forBranch($branchId)->get(); $subjects=Subject::forBranch($branchId)->get(); $years=AcademicYear::forBranch($branchId)->get(); $terms=Term::forBranch($branchId)->get(); return view('teaching.schedule-create',compact('teachers','classes','subjects','years','terms'));
    }

    public function storeSchedule(StoreTeacherScheduleRequest $request)
    {
        $data=$request->validated(); $class=SchoolClass::findOrFail($data['class_id']); $this->authorizeBranch($class->branch_id); $conflict=TeacherSchedule::where('branch_id',$class->branch_id)->where('day_of_week',$data['day_of_week'])->where(function($q)use($data){$q->where('teacher_id',$data['teacher_id'])->orWhere('class_id',$data['class_id'])->orWhere('room',$data['room']??'');})->where('start_time','<',$data['end_time'])->where('end_time','>',$data['start_time'])->exists(); abort_if($conflict,422,'ម៉ោងសិក្សានេះមានការប៉ះទង្គិច។'); $schedule=TeacherSchedule::create([...$data,'branch_id'=>$class->branch_id]); AuditService::log('teacher_schedule.created','បង្កើតកាលវិភាគគ្រូ',$schedule,[], $schedule->toArray()); return redirect()->route('teacher-schedules.index')->with('success','បានរក្សាទុកកាលវិភាគដោយជោគជ័យ។');
    }

    public function destroySchedule(TeacherSchedule $schedule)
    { $this->authorizeBranch($schedule->branch_id); $schedule->update(['status'=>'inactive']); return back()->with('success','បានបិទកាលវិភាគ។'); }
    private function branchId(Request $request):?int{return $request->user()->isSuperAdmin()?null:$request->user()->branch_id;}
    private function authorizeBranch(int $id):void{abort_unless(auth()->user()->isSuperAdmin()||auth()->user()->branch_id===$id,403);}
}
