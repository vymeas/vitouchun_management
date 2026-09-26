<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTeacherRegistrationRequest;
use App\Http\Requests\UpdateTeacherRegistrationRequest;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherRegistrationController extends Controller
{
    public function create(Request $request)
    {
        $branches=auth()->user()->isSuperAdmin()?Branch::active()->orderBy('name')->get():collect(); return view('teaching.registration-create',compact('branches'));
    }

    public function store(StoreTeacherRegistrationRequest $request)
    {
        $data=$request->validated(); $branchId=auth()->user()->isSuperAdmin()?($data['branch_id']??null):auth()->user()->branch_id; abort_unless($branchId,422,'សូមជ្រើសរើសសាខា។');
        $user=DB::transaction(function()use($data,$branchId,$request){
            $user=User::create(['username'=>$data['username'],'full_name'=>$data['full_name'],'full_name_kh'=>$data['full_name_kh'],'email'=>$data['email']??null,'phone'=>$data['phone']??null,'branch_id'=>$branchId,'role'=>'teacher','status'=>'active','is_deleted'=>false,'password'=>Hash::make($data['password'])]);
            $photo=$request->hasFile('photo')?$request->file('photo')->store('employees','public'):null;
            $employee=Employee::create(['employee_id'=>'EMP-'.str_pad((string)(Employee::withTrashed()->count()+1),5,'0',STR_PAD_LEFT),'name_kh'=>$data['full_name_kh'],'name_en'=>$data['full_name'],'gender'=>$data['gender'],'dob'=>$data['dob']??null,'phone'=>$data['phone']??null,'email'=>$data['email']??null,'photo'=>$photo,'branch_id'=>$branchId,'joining_date'=>$data['joining_date'],'employment_type'=>'full_time','basic_salary'=>$data['basic_salary']??0,'status'=>'active','user_id'=>$user->id]);
            $profile=TeacherProfile::create(['user_id'=>$user->id,'employee_id'=>$employee->id,'branch_id'=>$branchId,'teacher_code'=>$this->nextCode(),'national_id'=>$data['national_id']??null,'specialization'=>$data['specialization']??null,'education_level'=>$data['education_level']??null,'major'=>$data['major']??null,'institution'=>$data['institution']??null,'graduation_year'=>$data['graduation_year']??null,'remark'=>$data['remark']??null]);
            AuditService::log('teacher.registered','ចុះឈ្មោះគ្រូ: '.$profile->teacher_code,$profile,[], $profile->toArray()); return $user;
        });
        return redirect()->route('teachers.show',$user)->with('success','បានចុះឈ្មោះគ្រូដោយជោគជ័យ។');
    }

    public function show(User $teacher)
    { abort_unless($teacher->role==='teacher',404); $this->authorizeBranch($teacher->branch_id); $teacher->load(['branch','teacherProfile.employee','teacherClassAssignments.schoolClass','teacherSubjectAssignments.subject','teacherSubjectAssignments.schoolClass','teacherSchedules.subject','teacherSchedules.schoolClass']); return view('teaching.teacher-show',compact('teacher')); }

    public function edit(User $teacher, Request $request)
    { abort_unless($teacher->role==='teacher',404); $this->authorizeBranch($teacher->branch_id); $branches=auth()->user()->isSuperAdmin()?Branch::active()->orderBy('name')->get():collect(); $teacher->load('teacherProfile.employee'); return view('teaching.registration-edit',compact('teacher','branches')); }

    public function update(UpdateTeacherRegistrationRequest $request, User $teacher)
    { abort_unless($teacher->role==='teacher',404); $this->authorizeBranch($teacher->branch_id); $data=$request->validated(); DB::transaction(function()use($data,$teacher){$teacher->update(['username'=>$data['username'],'full_name'=>$data['full_name'],'full_name_kh'=>$data['full_name_kh'],'email'=>$data['email']??null,'phone'=>$data['phone']??null]); if(!empty($data['password']))$teacher->update(['password'=>Hash::make($data['password'])]); $teacher->teacherProfile?->update(['national_id'=>$data['national_id']??null,'specialization'=>$data['specialization']??null,'education_level'=>$data['education_level']??null,'major'=>$data['major']??null,'institution'=>$data['institution']??null,'graduation_year'=>$data['graduation_year']??null,'remark'=>$data['remark']??null]); $teacher->teacherProfile?->employee?->update(['name_kh'=>$data['full_name_kh'],'name_en'=>$data['full_name'],'gender'=>$data['gender'],'dob'=>$data['dob']??null,'phone'=>$data['phone']??null,'email'=>$data['email']??null,'joining_date'=>$data['joining_date'],'basic_salary'=>$data['basic_salary']??0]);}); return redirect()->route('teachers.show',$teacher)->with('success','បានកែប្រែព័ត៌មានគ្រូដោយជោគជ័យ។'); }

    private function nextCode():string{return sprintf('TCH-%05d',TeacherProfile::count()+1);} private function authorizeBranch(?int $id):void{abort_unless(auth()->user()->isSuperAdmin()||auth()->user()->branch_id===$id,403);}
}
