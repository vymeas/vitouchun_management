<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingTest extends TestCase
{
    use RefreshDatabase;

    private function setupTeaching(): array
    {
        $branch=Branch::create(['code'=>'TCH','name'=>'Teaching','status'=>'active']);
        $admin=User::factory()->create(['branch_id'=>$branch->id,'role'=>'super_admin']);
        $teacher=User::factory()->create(['branch_id'=>$branch->id,'role'=>'teacher','status'=>'active']);
        $year=AcademicYear::create(['name'=>'2026-2027','start_date'=>'2026-09-01','end_date'=>'2027-08-31','is_active'=>true,'branch_id'=>$branch->id]);
        $grade=Grade::create(['name'=>'Grade 1','level'=>1,'branch_id'=>$branch->id]);
        $class=SchoolClass::create(['academic_year_id'=>$year->id,'grade_id'=>$grade->id,'name'=>'1A','capacity'=>30,'branch_id'=>$branch->id]);
        $subject=Subject::create(['code'=>'MATH','name_kh'=>'គណិតវិទ្យា','credit'=>1,'type'=>'core','branch_id'=>$branch->id]);
        return compact('branch','admin','teacher','year','class','subject');
    }

    public function test_teacher_class_assignment_is_saved_and_duplicates_rejected(): void
    {
        $d=$this->setupTeaching(); $payload=['teacher_id'=>$d['teacher']->id,'class_id'=>$d['class']->id,'academic_year_id'=>$d['year']->id,'role'=>'class_teacher'];
        $this->actingAs($d['admin'])->post(route('teacher-assignments.store'),$payload)->assertRedirect(route('teacher-assignments.index'));
        $this->actingAs($d['admin'])->post(route('teacher-assignments.store'),$payload)->assertStatus(422);
        $this->assertDatabaseCount('teacher_class_assignments',1);
    }

    public function test_schedule_conflict_is_rejected(): void
    {
        $d=$this->setupTeaching(); $payload=['teacher_id'=>$d['teacher']->id,'class_id'=>$d['class']->id,'subject_id'=>$d['subject']->id,'academic_year_id'=>$d['year']->id,'day_of_week'=>'Monday','start_time'=>'08:00','end_time'=>'09:00','room'=>'R1'];
        $this->actingAs($d['admin'])->post(route('teacher-schedules.store'),$payload)->assertRedirect(route('teacher-schedules.index'));
        $payload['start_time']='08:30'; $payload['end_time']='09:30';
        $this->actingAs($d['admin'])->post(route('teacher-schedules.store'),$payload)->assertStatus(422);
    }
}
