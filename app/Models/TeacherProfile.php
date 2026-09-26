<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TeacherProfile extends Model
{
    protected $fillable=['user_id','employee_id','branch_id','teacher_code','national_id','specialization','education_level','major','institution','graduation_year','remark'];
    public function user(){return $this->belongsTo(User::class);} public function employee(){return $this->belongsTo(Employee::class);} public function branch(){return $this->belongsTo(Branch::class);} public function classAssignments(){return $this->hasMany(TeacherClassAssignment::class,'teacher_id','user_id');} public function subjectAssignments(){return $this->hasMany(TeacherSubjectAssignment::class,'teacher_id','user_id');} public function schedules(){return $this->hasMany(TeacherSchedule::class,'teacher_id','user_id');}
}
